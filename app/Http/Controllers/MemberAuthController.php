<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Member;
use App\Models\MemberCoupon;
use App\Models\Referrer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class MemberAuthController extends Controller
{
    public function welcome(Request $request): View|RedirectResponse
    {
        if ($this->member($request)) {
            return redirect()->route('member.profile');
        }

        return view('members.welcome');
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($this->member($request)) {
            return redirect()->route('member.profile');
        }

        $googlePrefill = $request->session()->pull('member_google_prefill', []);
        $draft = $request->session()->get('member_draft', []);

        return view('members.register', [
            'googleProfile' => array_merge($googlePrefill, $draft),
            'googleReady' => $this->googleReady(),
        ]);
    }

    public function storeDetails(Request $request): RedirectResponse
    {
        $google = $request->session()->get('member_google', []);
        $existingDraft = $request->session()->get('member_draft', []);
        $hasSavedPassword = filled($existingDraft['password'] ?? null);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('members')->ignore($google['member_id'] ?? null)],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'mobile' => ['required', 'string', 'max:30'],
            'mobile_2' => ['nullable', 'string', 'max:30', 'different:mobile'],
            'address' => ['required', 'string', 'max:1000'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'password' => [($google || $hasSavedPassword) ? 'nullable' : 'required', 'nullable', 'confirmed', 'min:6'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
            'referral_code' => ['nullable', 'string', 'max:40', function (string $attribute, mixed $value, \Closure $fail): void {
                if (filled($value) && !Referrer::whereRaw('UPPER(code) = ?', [mb_strtoupper(trim((string) $value))])->where('is_active', true)->exists()) {
                    $fail('This referral code is invalid or inactive.');
                }
            }],
        ]);

        unset($validated['profile_photo']);
        if (!filled($validated['password'] ?? null) && $hasSavedPassword) {
            $validated['password'] = $existingDraft['password'];
        }
        $uploadedAvatar = $request->file('profile_photo')
            ? Storage::url($request->file('profile_photo')->store('member-avatars', 'public'))
            : null;

        $request->session()->put('member_draft', array_merge($existingDraft, $validated, [
            'google_id' => $google['google_id'] ?? null,
            'avatar_url' => $uploadedAvatar ?: ($existingDraft['avatar_url'] ?? ($google['avatar_url'] ?? null)),
            'email_verified_at' => !empty($google['email']) ? now() : null,
            'marketing_consent_at' => null,
        ]));

        return redirect()->route('member.division');
    }

    public function division(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('member_draft')) {
            return redirect()->route('member.register');
        }

        return view('members.division', [
            'divisions' => Member::DIVISIONS,
            'selectedGender' => data_get($request->session()->get('member_draft', []), 'gender', 'neutral'),
            'selectedDivision' => $request->session()->get('member_editing')
                ? $this->member($request)?->division
                : null,
            'editing' => (bool) $request->session()->get('member_editing'),
        ]);
    }

    public function finish(Request $request): RedirectResponse
    {
        $draft = $request->session()->get('member_draft');
        if (!$draft) {
            return redirect()->route('member.register');
        }

        $validated = $request->validate([
            'division' => ['required', Rule::in(array_keys(Member::DIVISIONS))],
        ]);

        $editing = $request->session()->pull('member_editing', false);
        $member = $editing ? Member::find($draft['member_id'] ?? null) : null;
        $values = array_merge($draft, $validated, [
            'password' => filled($draft['password'] ?? null)
                ? $draft['password']
                : Member::where('email', $draft['email'])->value('password'),
        ]);
        unset($values['member_id']);

        $member = DB::transaction(function () use ($member, $values, $editing, $draft): Member {
            if ($member) {
                $member->update($values);
                return $member;
            }

            $referrer = filled($draft['referral_code'] ?? null)
                ? Referrer::where('code', mb_strtoupper($draft['referral_code']))->where('is_active', true)->lockForUpdate()->first()
                : null;
            unset($values['referral_code']);
            $values['referred_by_id'] = $referrer?->id;
            $existingMember = Member::where('email', $draft['email'])->first();
            $isNewMembership = !$existingMember || !$existingMember->division;
            $created = Member::updateOrCreate(['email' => $draft['email']], $values);

            if ($referrer && $isNewMembership) {
                $referrer->increment('successful_referrals');

                $referralCoupon = Coupon::firstOrCreate(
                    ['code' => 'REFERRAL5'],
                    [
                        'title' => 'Referral Welcome Reward',
                        'discount_type' => Coupon::TYPE_PERCENTAGE,
                        'discount_value' => 5,
                        'min_order_amount' => 0,
                        'status' => Coupon::STATUS_ACTIVE,
                        'new_customer_only' => false,
                        'show_as_popup' => false,
                    ]
                );

                MemberCoupon::firstOrCreate([
                    'member_id' => $created->id,
                    'coupon_id' => $referralCoupon->id,
                ], [
                    'source' => 'referral',
                    'status' => 'available',
                ]);
            }

            return $created;
        });

        $request->session()->forget([
            'member_draft',
            'member_google',
            'member_google_prefill',
        ]);
        $request->session()->put('member_id', $member->id);
        $request->session()->regenerate();

        return redirect()->route('member.profile')->with('member_welcome', true);
    }

    public function login(): View
    {
        return view('members.login', [
            'googleReady' => $this->googleReady(),
        ]);
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $member = Member::where('email', $validated['email'])->first();

        if (!$member || !$member->password || !Hash::check($validated['password'], $member->password)) {
            return back()->withErrors(['email' => 'Email or password is incorrect.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->session()->put('member_id', $member->id);
        return redirect()->route('member.profile');
    }

    public function googleRedirect(): RedirectResponse
    {
        if (!$this->googleReady()) {
            return redirect()
                ->route('member.form')
                ->withErrors([
                    'google' => 'Google sign-in is being configured. Please use the member form for now.',
                ]);
        }

        return Socialite::driver('google')
            ->scopes([
                'https://www.googleapis.com/auth/user.birthday.read',
                'https://www.googleapis.com/auth/user.gender.read',
                'https://www.googleapis.com/auth/user.phonenumbers.read',
                'https://www.googleapis.com/auth/user.addresses.read',
            ])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function googleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            report($exception);
            return redirect()->route('member.form')->withErrors(['google' => 'Google sign-in could not be completed. Please try again.']);
        }

        $peopleProfile = $this->googlePeopleProfile($googleUser->token);

        $member = Member::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($member && $member->division) {
            $member->update([
                'google_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar() ?: $member->avatar_url,
                'email_verified_at' => $member->email_verified_at ?: now(),
            ]);
            $request->session()->regenerate();
            $request->session()->put('member_id', $member->id);
            return redirect()->route('member.profile');
        }

        $googleProfile = [
            'member_id' => $member?->id,
            'google_id' => $googleUser->getId(),
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'avatar_url' => $googleUser->getAvatar(),
            'gender' => $peopleProfile['gender'],
            'mobile' => $peopleProfile['mobile'],
            'mobile_2' => $peopleProfile['mobile_2'],
            'date_of_birth' => $peopleProfile['date_of_birth'],
            'address' => $peopleProfile['address'],
        ];

        $request->session()->put('member_google', $googleProfile);
        $request->session()->put('member_google_prefill', $googleProfile);
        return redirect()->route('member.form');
    }

    public function profile(Request $request): View|RedirectResponse
    {
        $member = $this->member($request);
        if (!$member) {
            return redirect()->route('member.login');
        }

        $purchaseCount = $member->orders()->where('status', '!=', \App\Models\Order::STATUS_CANCELLED)->count();
        $milestones = \App\Models\PurchaseMilestone::with('coupon')->where('is_active', true)->orderBy('step')->get();

        return view('members.profile', [
            'member' => $member,
            'walletCoupons' => $member->walletCoupons()->with('coupon')->latest()->get(),
            'purchaseCount' => $purchaseCount,
            'milestones' => $milestones,
            'journeyLength' => max(1, (int) \App\Models\PurchaseMilestone::max('step')),
        ]);
    }

    public function collectMilestone(Request $request, \App\Models\PurchaseMilestone $milestone): RedirectResponse
    {
        $member = $this->member($request);
        if (!$member) {
            return redirect()->route('member.login');
        }

        $purchaseCount = $member->orders()->where('status', '!=', \App\Models\Order::STATUS_CANCELLED)->count();
        if (!$milestone->is_active || !$milestone->coupon) {
            return redirect()->to(route('member.profile').'#wallet')->withErrors(['milestone' => 'This milestone does not have an active coupon reward.']);
        }
        if ($purchaseCount < $milestone->step) {
            return redirect()->to(route('member.profile').'#wallet')->withErrors(['milestone' => 'Complete purchase '.$milestone->step.' before collecting this coupon.']);
        }
        if (!$milestone->coupon->isUsableNow()) {
            return redirect()->to(route('member.profile').'#wallet')->withErrors(['milestone' => 'This milestone coupon is inactive or expired. Please contact Grey Stone.']);
        }

        \App\Models\MemberCoupon::firstOrCreate([
            'member_id' => $member->id,
            'coupon_id' => $milestone->coupon_id,
        ], ['source' => 'milestone:'.$milestone->step]);

        return redirect()->to(route('member.profile').'#wallet')->with('milestone_collected', $milestone->step);
    }

    public function edit(Request $request): View|RedirectResponse
    {
        $member = $this->member($request);
        if (!$member) {
            return redirect()->route('member.login');
        }

        $savedDraft = $request->session()->get('member_editing')
            ? $request->session()->get('member_draft', [])
            : [];

        return view('members.register', [
            'googleProfile' => $savedDraft ?: [
                'name' => $member->name,
                'email' => $member->email,
                'gender' => $member->gender,
                'mobile' => $member->mobile,
                'mobile_2' => $member->mobile_2,
                'address' => $member->address,
                'date_of_birth' => $member->date_of_birth->format('Y-m-d'),
                'avatar_url' => $member->avatar_url,
            ],
            'googleReady' => false,
            'editing' => true,
        ]);
    }

    public function updateDetails(Request $request): RedirectResponse
    {
        $member = $this->member($request);
        if (!$member) {
            return redirect()->route('member.login');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('members')->ignore($member->id)],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'mobile' => ['required', 'string', 'max:30'],
            'mobile_2' => ['nullable', 'string', 'max:30', 'different:mobile'],
            'address' => ['required', 'string', 'max:1000'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
        ]);

        unset($validated['profile_photo']);
        $uploadedAvatar = $request->file('profile_photo')
            ? Storage::url($request->file('profile_photo')->store('member-avatars', 'public'))
            : null;

        $existingDraft = $request->session()->get('member_draft', []);
        $request->session()->put('member_draft', array_merge($member->only([
            'password', 'google_id', 'avatar_url', 'email_verified_at', 'marketing_consent_at',
        ]), $existingDraft, $validated, [
            'member_id' => $member->id,
            'avatar_url' => $uploadedAvatar ?: ($existingDraft['avatar_url'] ?? $member->avatar_url),
        ]));
        $request->session()->put('member_editing', true);

        return redirect()->route('member.division');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $member = $this->member($request);
        if (!$member) {
            return redirect()->route('member.login');
        }

        $validated = $request->validate([
            'profile_photo' => ['required', 'image', 'max:5120'],
        ]);
        $member->update([
            'avatar_url' => Storage::url($validated['profile_photo']->store('member-avatars', 'public')),
        ]);

        return redirect()->route('member.profile');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('member_id');
        $request->session()->regenerateToken();
        return redirect()->route('member.login');
    }

    private function member(Request $request): ?Member
    {
        return Member::find($request->session()->get('member_id'));
    }

    private function googleReady(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    private function googlePeopleProfile(string $accessToken): array
    {
        $empty = [
            'gender' => null,
            'mobile' => null,
            'mobile_2' => null,
            'date_of_birth' => null,
            'address' => null,
        ];

        try {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(10)
                ->get('https://people.googleapis.com/v1/people/me', [
                    'personFields' => 'birthdays,genders,phoneNumbers,addresses',
                ]);

            if (!$response->successful()) {
                report(new \RuntimeException(
                    'Google People API profile request failed with status '.$response->status()
                ));
                return $empty;
            }

            $profile = $response->json();
            $gender = strtolower((string) data_get($profile, 'genders.0.value'));
            $phones = collect($profile['phoneNumbers'] ?? [])
                ->sortByDesc(fn (array $phone) => (bool) data_get($phone, 'metadata.primary'))
                ->map(fn (array $phone) => trim((string) ($phone['canonicalForm'] ?? $phone['value'] ?? '')))
                ->filter()
                ->unique()
                ->values();
            $birthday = collect($profile['birthdays'] ?? [])
                ->first(fn (array $item) => filled(data_get($item, 'date.year'))
                    && filled(data_get($item, 'date.month'))
                    && filled(data_get($item, 'date.day')));
            $birthdayDate = $birthday
                ? sprintf(
                    '%04d-%02d-%02d',
                    data_get($birthday, 'date.year'),
                    data_get($birthday, 'date.month'),
                    data_get($birthday, 'date.day')
                )
                : null;
            $address = collect($profile['addresses'] ?? [])
                ->sortByDesc(fn (array $item) => (bool) data_get($item, 'metadata.primary'))
                ->map(fn (array $item) => trim((string) ($item['formattedValue'] ?? '')))
                ->first();

            return [
                'gender' => in_array($gender, ['male', 'female'], true) ? $gender : null,
                'mobile' => $phones->get(0),
                'mobile_2' => $phones->get(1),
                'date_of_birth' => $birthdayDate,
                'address' => $address,
            ];
        } catch (Throwable $exception) {
            report($exception);
            return $empty;
        }
    }
}

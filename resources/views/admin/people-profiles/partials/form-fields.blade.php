<div class="people-hr-form">
    <section>
        <h4>Personal Details</h4>
        <div class="people-profile-fields">
            <label>Photo<input id="{{ $formPrefix }}_photo" type="file" name="photo" accept="image/*"></label>
            <label>Role {!! $infoHelp('profile_type') !!}<select id="{{ $formPrefix }}_profile_type" name="profile_type" required>@foreach($types as $key => $label)<option value="{{ $key }}" @selected(old('profile_type', $profile?->profile_type) === $key)>{{ $label }}</option>@endforeach</select></label>
            <label>Name<input id="{{ $formPrefix }}_name" name="name" value="{{ old('name', $profile?->name) }}" placeholder="Person name" required></label>
            <label>Designation / Role<input id="{{ $formPrefix }}_title" name="title" value="{{ old('title', $profile?->title) }}" placeholder="Example: Co-Founder & Vice President"></label>
            <label>Email<input id="{{ $formPrefix }}_email" type="email" name="email" value="{{ old('email', $profile?->email) }}" placeholder="person@example.com"></label>
            <label>Phone<input id="{{ $formPrefix }}_phone" name="phone" value="{{ old('phone', $profile?->phone) }}" placeholder="01XXXXXXXXX"></label>
            <label>Optional Phone<input id="{{ $formPrefix }}_optional_phone" name="optional_phone" value="{{ old('optional_phone', $profile?->optional_phone) }}" placeholder="01XXXXXXXXX"></label>
            <label>Emergency Contact<input id="{{ $formPrefix }}_emergency_contact" name="emergency_contact" value="{{ old('emergency_contact', $profile?->emergency_contact) }}" placeholder="Emergency phone/name"></label>
            <label>NID / Document<input id="{{ $formPrefix }}_nid_or_document" name="nid_or_document" value="{{ old('nid_or_document', $profile?->nid_or_document) }}" placeholder="Optional document reference"></label>
            <label>Joining Date<input id="{{ $formPrefix }}_joining_date" type="date" name="joining_date" value="{{ old('joining_date', $profile?->joining_date?->format('Y-m-d')) }}"></label>
            <label class="people-profile-wide">Address<textarea id="{{ $formPrefix }}_address" name="address" rows="2" placeholder="Personal address">{{ old('address', $profile?->address) }}</textarea></label>
        </div>
    </section>

    <section>
        <h4>Permissions</h4>
        <div class="people-profile-fields">
            <input id="{{ $formPrefix }}_user_id" type="hidden" name="user_id" value="{{ old('user_id', $profile?->user_id) }}">
            <input id="{{ $formPrefix }}_admin_role" type="hidden" name="admin_role" value="{{ old('admin_role', $profile?->admin_role ?? 'admin') }}">
            <label class="people-profile-toggle people-profile-wide"><input id="{{ $formPrefix }}_permission_override_enabled" type="checkbox" name="permission_override_enabled" value="1" @checked(old('permission_override_enabled', $profile?->permission_override_enabled ?? false)) data-permission-override-toggle> Customize this person’s permissions {!! $infoHelp('custom_permissions') !!}</label>
            <label>New Login Password {!! $infoHelp('password') !!}<input id="{{ $formPrefix }}_admin_password" type="password" name="admin_password" autocomplete="new-password" placeholder="Required only for new admin"></label>
            <label>Confirm Password {!! $infoHelp('password') !!}<input id="{{ $formPrefix }}_admin_password_confirmation" type="password" name="admin_password_confirmation" autocomplete="new-password"></label>
            @if(auth()->user()->is_root_admin)
                <label class="people-profile-toggle"><input id="{{ $formPrefix }}_is_root_admin" type="checkbox" name="is_root_admin" value="1"> Root Admin {!! $infoHelp('root_admin') !!}</label>
                <label>Root Passcode {!! $infoHelp('root_passcode') !!}<input id="{{ $formPrefix }}_root_admin_passcode" type="password" name="root_admin_passcode" placeholder="Required for root"></label>
            @endif
        </div>

        <div class="people-permission-block" data-permission-override-panel>
            <strong>Custom Permissions {!! $infoHelp('custom_permissions') !!}</strong>
            <div class="admin-permission-grid">
                @foreach($modules as $module => $meta)
                    <div class="admin-permission-card">
                        <div class="admin-permission-head"><span><i class="fa-solid {{ $meta['icon'] }}"></i><strong>{{ $meta['label'] }}</strong></span></div>
                        <div class="admin-permission-options">@foreach($actions as $action => $label)<label><input type="checkbox" name="admin_permissions[]" value="{{ $module }}.{{ $action }}"><span>{{ $label }}</span></label>@endforeach</div>
                    </div>
                @endforeach
            </div>
            <div class="admin-sensitive-permissions">@foreach($sensitivePermissions as $permission => $label)<label><input type="checkbox" name="admin_permissions[]" value="{{ $permission }}"><span>{{ $label }}</span></label>@endforeach</div>
        </div>
    </section>

    <section>
        <h4>Referral & Payroll</h4>
        <div class="people-profile-fields">
            <label class="people-profile-toggle people-profile-wide"><input id="{{ $formPrefix }}_referral_enabled" type="checkbox" name="referral_enabled" value="1" @checked(old('referral_enabled', $profile?->referral_enabled ?? false))> Can refer customers {!! $infoHelp('referral_enabled') !!}</label>
            <label>Linked Member {!! $infoHelp('linked_member') !!}<select id="{{ $formPrefix }}_member_id" name="member_id"><option value="">Not linked</option>@foreach($members as $member)<option value="{{ $member->id }}" @selected((string) old('member_id', $profile?->member_id) === (string) $member->id)>{{ $member->name }} — {{ $member->email }}</option>@endforeach</select></label>
            <label>Linked Referrer Code {!! $infoHelp('linked_referrer') !!}<select id="{{ $formPrefix }}_referrer_id" name="referrer_id"><option value="">Auto create if referral is on</option>@foreach($referrers as $referrer)<option value="{{ $referrer->id }}" @selected((string) old('referrer_id', $profile?->referrer_id) === (string) $referrer->id)>{{ $referrer->name }} — {{ $referrer->code }}</option>@endforeach</select></label>
            <label>Payment Type {!! $infoHelp('payment_type') !!}<select id="{{ $formPrefix }}_payment_type" name="payment_type" required>@foreach($paymentTypes as $key => $label)<option value="{{ $key }}" @selected(old('payment_type', $profile?->payment_type ?? 'mixed') === $key)>{{ $label }}</option>@endforeach</select></label>
            <label>Salary Type {!! $infoHelp('salary_type') !!}<select id="{{ $formPrefix }}_salary_type" name="salary_type" required>@foreach($salaryTypes as $key => $label)<option value="{{ $key }}" @selected(old('salary_type', $profile?->salary_type ?? 'monthly') === $key)>{{ $label }}</option>@endforeach</select></label>
            <label>Salary Amount (৳) {!! $infoHelp('salary_amount') !!}<input id="{{ $formPrefix }}_salary_amount" type="number" name="salary_amount" min="0" step="0.01" value="{{ old('salary_amount', $profile?->salary_amount ?? 0) }}"></label>
            <label>Commission Rate (%) {!! $infoHelp('commission_rate') !!}<input id="{{ $formPrefix }}_commission_rate" type="number" name="commission_rate" min="0" max="100" step="0.01" value="{{ old('commission_rate', $profile?->commission_rate) }}" placeholder="Optional"></label>
            <label>Gift / Bonus Balance (৳) {!! $infoHelp('gift_balance') !!}<input id="{{ $formPrefix }}_gift_balance" type="number" name="gift_balance" min="0" step="0.01" value="{{ old('gift_balance', $profile?->gift_balance ?? 0) }}"></label>
            <label>Sort Order {!! $infoHelp('sort') !!}<input id="{{ $formPrefix }}_sort_order" type="number" name="sort_order" min="0" step="1" value="{{ old('sort_order', $profile?->sort_order ?? 0) }}"></label>
            <label class="people-profile-toggle"><input id="{{ $formPrefix }}_is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $profile?->is_active ?? true))> Active employee {!! $infoHelp('status') !!}</label>
        </div>
    </section>

    <section>
        <h4>Notes</h4>
        <div class="people-profile-fields">
            <label class="people-profile-wide">Role Notes {!! $infoHelp('role_notes') !!}<textarea id="{{ $formPrefix }}_role_notes" name="role_notes" rows="3" placeholder="Role, duty, responsibility and operator note">{{ old('role_notes', $profile?->role_notes) }}</textarea></label>
            <label class="people-profile-wide">Story / Contribution {!! $infoHelp('story') !!}<textarea id="{{ $formPrefix }}_story" name="story" rows="4" placeholder="Write why this person receives commission, gift or payment.">{{ old('story', $profile?->story) }}</textarea></label>
            <label class="people-profile-wide">Receipt / Tax Note {!! $infoHelp('tax_note') !!}<textarea id="{{ $formPrefix }}_tax_note" name="tax_note" rows="3" placeholder="Write receipt tag, tax explanation or payment note.">{{ old('tax_note', $profile?->tax_note) }}</textarea></label>
        </div>
    </section>
</div>

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $members = Member::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim($request->string('search'));
                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%"));
            })
            ->when($request->filled('gender'), fn ($query) => $query->where('gender', $request->string('gender')))
            ->when($request->filled('division'), fn ($query) => $query->where('division', $request->string('division')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.members.index', [
            'members' => $members,
            'divisions' => Member::DIVISIONS,
            'totalMembers' => Member::count(),
            'maleMembers' => Member::where('gender', 'male')->count(),
            'femaleMembers' => Member::where('gender', 'female')->count(),
        ]);
    }
}

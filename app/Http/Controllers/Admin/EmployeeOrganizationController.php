<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Designation;
use App\Models\DesignationResponsibility;
use App\Models\PeopleProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeOrganizationController extends Controller
{
    public function departments(): JsonResponse
    {
        return response()->json(Department::with('designations')->orderBy('sort_order')->get());
    }

    public function people(Request $request): JsonResponse
    {
        $profiles = PeopleProfile::query()
            ->with(['primaryDepartment:id,name,is_active', 'primaryDesignation:id,department_id,name,is_active'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = trim($request->string('search')->toString());
                $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"));
            })
            ->orderBy('name')->limit(50)->get();

        return response()->json($profiles->map(fn (PeopleProfile $profile): array => [
            'id' => $profile->id,
            'name' => $profile->name,
            'email' => $profile->email,
            'phone' => $profile->phone,
            'is_active' => (bool) $profile->is_active,
            'is_employee' => (bool) $profile->is_employee,
            'department' => $profile->primaryDepartment?->only(['id', 'name', 'is_active']),
            'designation' => $profile->primaryDesignation?->only(['id', 'department_id', 'name', 'is_active']),
        ])->values());
    }

    public function assign(Request $request, PeopleProfile $peopleProfile): JsonResponse
    {
        $data = $request->validate([
            'is_employee' => ['required', 'boolean'],
            'primary_department_id' => ['nullable', 'exists:departments,id'],
            'primary_designation_id' => ['nullable', 'exists:designations,id'],
        ]);

        if ($data['is_employee'] && (!$data['primary_department_id'] || !$data['primary_designation_id'])) {
            abort(422, 'An employee must have a department and designation.');
        }
        if (!empty($data['primary_designation_id']) && !Designation::whereKey($data['primary_designation_id'])->where('department_id', $data['primary_department_id'])->exists()) {
            abort(422, 'The designation must belong to the selected department.');
        }

        $peopleProfile->update([
            'is_employee' => $data['is_employee'],
            'primary_department_id' => $data['is_employee'] ? $data['primary_department_id'] : null,
            'primary_designation_id' => $data['is_employee'] ? $data['primary_designation_id'] : null,
        ]);

        return response()->json(['status' => 'success', 'profile' => $peopleProfile->fresh(['primaryDepartment', 'primaryDesignation'])]);
    }

    public function storeDepartment(Request $request): JsonResponse
    {
        return response()->json(Department::create($request->validate([
            'name' => ['required', 'string', 'max:160', 'unique:departments,name'],
            'description' => ['nullable', 'string'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean'],
        ])), 201);
    }

    public function updateDepartment(Request $request, Department $department): JsonResponse
    {
        $department->update($request->validate([
            'name' => ['required', 'string', 'max:160', 'unique:departments,name,'.$department->id],
            'description' => ['nullable', 'string'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean'],
        ]));
        return response()->json($department->fresh());
    }

    public function storeDesignation(Request $request): JsonResponse
    {
        $data = $request->validate(['department_id' => ['required', 'exists:departments,id'], 'name' => ['required', 'string', 'max:160'], 'description' => ['nullable', 'string'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean']]);
        $designation = Designation::create($data);
        return response()->json($designation->load('department'), 201);
    }

    public function updateDesignation(Request $request, Designation $designation): JsonResponse
    {
        $designation->update($request->validate(['department_id' => ['required', 'exists:departments,id'], 'name' => ['required', 'string', 'max:160'], 'description' => ['nullable', 'string'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean']]));
        return response()->json($designation->fresh('department'));
    }

    public function responsibilities(Designation $designation): JsonResponse
    {
        return response()->json([
            'designation' => $designation->only(['id', 'department_id', 'name', 'is_active']),
            'responsibilities' => $designation->responsibilities()->orderBy('sort_order')->orderBy('id')->get(['id', 'designation_id', 'description', 'sort_order', 'is_active']),
        ]);
    }

    public function storeResponsibility(Request $request, Designation $designation): JsonResponse
    {
        $data = $request->validate(['description' => ['required', 'string', 'max:5000'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean']]);
        return response()->json($designation->responsibilities()->create($data), 201);
    }

    public function updateResponsibility(Request $request, DesignationResponsibility $responsibility): JsonResponse
    {
        $responsibility->update($request->validate(['description' => ['required', 'string', 'max:5000'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean']]));
        return response()->json($responsibility->fresh());
    }

    public function destroyResponsibility(DesignationResponsibility $responsibility): JsonResponse
    {
        $responsibility->delete();
        return response()->json(['status' => 'success']);
    }

    public function directory(Request $request): JsonResponse
    {
        $profiles = PeopleProfile::query()->where('is_employee', true)->where('is_active', true)
            ->with(['primaryDepartment', 'primaryDesignation', 'assignments.department', 'assignments.designation', 'additionalResponsibilities'])
            ->when($request->filled('department_id'), fn ($q) => $q->where('primary_department_id', $request->integer('department_id')))
            ->when($request->filled('designation_id'), fn ($q) => $q->where('primary_designation_id', $request->integer('designation_id')))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.trim($request->string('search')).'%'))
            ->orderBy('name')->paginate(50)->withQueryString();
        $profiles->setCollection($profiles->getCollection()->map(fn (PeopleProfile $profile): array => [
            'id' => $profile->id,
            'name' => $profile->name,
            'phone' => $profile->phone,
            'email' => $profile->email,
            'is_active' => (bool) $profile->is_active,
            'department' => $profile->primaryDepartment?->only(['id', 'name', 'is_active']),
            'designation' => $profile->primaryDesignation?->only(['id', 'department_id', 'name', 'is_active']),
            'additional_assignments' => $profile->assignments->map(fn ($assignment) => [
                'id' => $assignment->id,
                'department_id' => $assignment->department_id,
                'department_name' => $assignment->department?->name,
                'designation_id' => $assignment->designation_id,
                'designation_name' => $assignment->designation?->name,
                'assignment_label' => $assignment->assignment_label,
                'is_active' => (bool) $assignment->is_active,
            ])->values(),
            'additional_responsibilities' => $profile->additionalResponsibilities->map(fn ($duty) => [
                'id' => $duty->id,
                'description' => $duty->description,
                'sort_order' => (int) $duty->sort_order,
                'is_active' => (bool) $duty->is_active,
            ])->values(),
        ]));
        return response()->json($profiles);
    }

    public function printJobDescription(Request $request, Designation $designation): View
    {
        $designation->load(['department', 'responsibilities' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('id')]);
        return view('admin.employee-job-description.print', [
            'department' => $designation->department,
            'designation' => $designation,
            'responsibilities' => $designation->responsibilities,
            'personName' => $request->string('name')->trim()->toString() ?: null,
            'printDate' => now(),
        ]);
    }
}

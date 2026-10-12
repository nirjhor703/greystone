<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('departments')) Schema::create('departments', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (! Schema::hasTable('designations')) Schema::create('designations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['department_id', 'name']);
        });

        if (! Schema::hasTable('designation_responsibilities')) Schema::create('designation_responsibilities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('designation_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('people_profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('people_profiles', 'is_employee')) $table->boolean('is_employee')->default(false)->after('profile_type');
            if (! Schema::hasColumn('people_profiles', 'primary_department_id')) $table->foreignId('primary_department_id')->nullable()->after('is_employee')->constrained('departments')->nullOnDelete();
            if (! Schema::hasColumn('people_profiles', 'primary_designation_id')) $table->foreignId('primary_designation_id')->nullable()->after('primary_department_id')->constrained('designations')->nullOnDelete();
        });

        if (! Schema::hasTable('employee_assignments')) Schema::create('employee_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('people_profile_id')->constrained('people_profiles')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('designation_id')->constrained()->restrictOnDelete();
            $table->string('assignment_label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['people_profile_id', 'department_id', 'designation_id'], 'emp_assign_profile_dept_desig_uq');
        });

        if (Schema::hasTable('employee_assignments') && ! DB::table('information_schema.STATISTICS')->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())->where('TABLE_NAME', 'employee_assignments')->where('INDEX_NAME', 'emp_assign_profile_dept_desig_uq')->exists()) {
            Schema::table('employee_assignments', function (Blueprint $table): void {
                $table->unique(['people_profile_id', 'department_id', 'designation_id'], 'emp_assign_profile_dept_desig_uq');
            });
        }

        if (! Schema::hasTable('employee_responsibilities')) Schema::create('employee_responsibilities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('people_profile_id')->constrained('people_profiles')->cascadeOnDelete();
            $table->text('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $departments = [
            'Production & Supply Chain',
            'Marketing & Growth',
            'Sales & Customer Service',
            'Warehouse & Fulfillment',
        ];

        foreach ($departments as $sort => $name) {
            if (! DB::table('departments')->where('name', $name)->exists()) {
                DB::table('departments')->insert(['name' => $name, 'sort_order' => $sort + 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        $designationMap = [
            'Production & Supply Chain' => [
                'Head of Production / Production Manager',
                'Production Merchandiser / Coordinator',
                'QA/QC Inspector',
                'Production & Inventory Coordinator',
                'Sourcing & Procurement Executive',
            ],
            'Marketing & Growth' => [
                'Marketing & Growth Executive / Manager',
                'Digital Marketing / Performance Marketer',
                'Content & Social Media Executive',
                'Offline Marketing & Activation Executive',
                'Creative Designer / Video Editor',
            ],
            'Sales & Customer Service' => [
                'Online Sales & Customer Service Executive',
                'B2B / Wholesale / Corporate Sales Executive',
            ],
            'Warehouse & Fulfillment' => [
                'Warehouse & Fulfillment Supervisor / Manager',
                'Picker & Packer',
            ],
        ];

        foreach ($designationMap as $departmentName => $names) {
            $departmentId = DB::table('departments')->where('name', $departmentName)->value('id');
            foreach ($names as $sort => $name) {
                if (! DB::table('designations')->where('department_id', $departmentId)->where('name', $name)->exists()) {
                    DB::table('designations')->insert(['department_id' => $departmentId, 'name' => $name, 'sort_order' => $sort + 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('people_profiles', function (Blueprint $table): void {
            $table->dropForeign(['primary_department_id']);
            $table->dropForeign(['primary_designation_id']);
            $table->dropColumn(['is_employee', 'primary_department_id', 'primary_designation_id']);
        });
        Schema::dropIfExists('employee_responsibilities');
        Schema::dropIfExists('employee_assignments');
        Schema::dropIfExists('designation_responsibilities');
        Schema::dropIfExists('designations');
        Schema::dropIfExists('departments');
    }
};

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'manage-university', 'manage-academic-structure', 'manage-curriculum',
            'manage-skills', 'manage-labor-market', 'manage-students',
            'review-ai-extractions', 'run-curriculum-analysis', 'run-student-analysis',
            'manage-recommendations', 'view-reports', 'manage-users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $matrix = [
            'super-admin' => $permissions,
            'university-admin' => $permissions,
            'department-admin' => [
                'manage-academic-structure', 'manage-curriculum', 'manage-skills',
                'manage-labor-market', 'manage-students', 'review-ai-extractions',
                'run-curriculum-analysis', 'run-student-analysis', 'manage-recommendations', 'view-reports',
            ],
            'instructor' => [
                'manage-curriculum', 'manage-skills', 'manage-students',
                'review-ai-extractions', 'run-curriculum-analysis', 'run-student-analysis', 'view-reports',
            ],
            'career-advisor' => [
                'manage-labor-market', 'manage-students', 'run-student-analysis',
                'manage-recommendations', 'view-reports',
            ],
            'employer' => ['manage-labor-market', 'view-reports'],
            'student' => ['run-student-analysis'],
            'graduate' => ['run-student-analysis'],
        ];

        foreach ($matrix as $roleName => $granted) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'])
                ->syncPermissions($granted);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
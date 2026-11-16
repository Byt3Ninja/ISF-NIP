<?php

namespace Tests\Support;

use App\Http\Middleware\LoginSecurityMiddleware;
use App\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Users, organizations, programs and startups for the audit regression tests.
 * Everything is written inside the test's transaction (see Tests\TestCase) and
 * rolled back afterwards. Roles are attached by explicit id, never through
 * allotRole(), so a test always gets exactly the seeded role it asks for.
 */
trait SecurityActors
{
    use FakesOutboundServices;

    protected function setUpSecurityActors(): void
    {
        $this->fakeOutboundServices();
        $this->withoutMiddleware([LoginSecurityMiddleware::class, EnsureEmailIsVerified::class]);
    }

    /** A user holding the seeded role whose default_for is $role (admin, organizationmanager, subadmin, registered, mentor, judger, serviceProvider). */
    protected function actor(string $role, array $attributes = []): User
    {
        $tag = Str::lower(Str::random(10));

        $id = DB::table('users')->insertGetId(array_merge([
            'first_name_en' => 'Audit',
            'last_name_en' => $role,
            'first_name_ar' => 'Audit',
            'last_name_ar' => $role,
            'email' => "audit-{$tag}@example.invalid",
            'phone' => '7' . random_int(100000000, 999999999),
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'must_change_password' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        DB::table('model_has_roles')->insert([
            'role_id' => $this->roleId($role),
            'model_type' => User::class,
            'model_id' => $id,
        ]);

        return User::findOrFail($id);
    }

    protected function roleId(string $role): int
    {
        return (int) DB::table('roles')->where('default_for', $role)->value('id');
    }

    /** Give a seeded role the named permissions (rolled back with the test). */
    protected function grant(string $role, array $permissions): void
    {
        foreach ($permissions as $name) {
            $permissionId = DB::table('permissions')->where('name', $name)->value('id')
                ?? DB::table('permissions')->insertGetId(['name' => $name, 'guard_name' => 'web', 'is_core' => 0, 'created_at' => now(), 'updated_at' => now()]);

            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $permissionId,
                'role_id' => $this->roleId($role),
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** Take a permission away from a seeded role (rolled back with the test). */
    protected function revoke(string $role, array $permissions): void
    {
        DB::table('role_has_permissions')
            ->where('role_id', $this->roleId($role))
            ->whereIn('permission_id', DB::table('permissions')->whereIn('name', $permissions)->pluck('id'))
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function organization(): int
    {
        return DB::table('organizations')->insertGetId([
            'name_en' => 'Audit Org ' . Str::random(6),
            'name_ar' => 'Audit Org',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function program(int $organizationId, array $attributes = []): int
    {
        return DB::table('cycles')->insertGetId(array_merge([
            'organization_id' => $organizationId,
            'title' => 'Audit Program ' . Str::random(6),
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }

    protected function step(int $cycleId, array $attributes = []): int
    {
        return DB::table('steps')->insertGetId(array_merge([
            'cycle_id' => $cycleId,
            'title' => 'Audit Step',
            'stype' => 1,
            'arr' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }

    /** A startup enrolled in $cycleId, plus its founder. @return array{0:int,1:User} */
    protected function startup(int $cycleId, array $attributes = []): array
    {
        $companyId = DB::table('companies')->insertGetId(array_merge([
            'name_en' => 'Audit Startup ' . Str::random(6),
            'name_ar' => 'Audit Startup',
            'cycle' => $cycleId,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        // The row is inserted without model events, so open its enrollment the way the
        // application would: staff lists and judging read enrollments, not the pointer.
        \App\CompanyEnrollment::syncFromPointer(\App\Company::findOrFail($companyId), null);

        // A founder belongs to a startup through users.company_id (companies has no owner column).
        $founder = $this->actor('registered', ['company_id' => $companyId, 'HaveCompany' => 1]);

        return [$companyId, $founder];
    }

    /** An organization manager scoped to $organizationId. */
    protected function manager(int $organizationId): User
    {
        return $this->actor('organizationmanager', ['organization_id' => $organizationId]);
    }
}

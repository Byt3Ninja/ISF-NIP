<?php
// app/Services/OrganizationService.php
namespace App\Services;

use App\User;
use App\Role;
use App\Organization;
use App\Mail\OrganizationCreatedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Repositories\OrganizationRepository;
use Illuminate\Http\UploadedFile;

class OrganizationService
{
    public function __construct(
        protected OrganizationRepository $organizationRepo,
        protected ?FileManagerService $fileManager = null
    ) {
        $this->fileManager = $fileManager ?? app(FileManagerService::class);
    }

    public function listOrganizations(?int $status = null)
    {
        $organizations = $this->organizationRepo->allWithRelations($status);
        $counts = $this->organizationRepo->counts($status);
        return compact('organizations', 'counts');
    }

    public function createOrganization(array $data,?UploadedFile $file, $countryCode): Organization
    {
        $img_url = $file 
            ? $this->fileManager->upload($file, 'organizations', 'organizations')
            : '/images/background/logo.png';
        return DB::transaction(function () use ($data, $img_url, $countryCode) {
            $password = $data['password'];

            $user = User::create([
                'first_name_en' => $data['firstName'],
                'last_name_en' => $data['lastName'],
                'first_name_ar' => $data['firstName'],
                'last_name_ar' => $data['lastName'],
                'email' => $data['email'],
                'gender' => $data['gender'],
                'phone' => $data['phone'],
                'HaveCompany' => 0,
                'password' => bcrypt($password),
                'country_code' => $countryCode,
            ]);

            $role = Role::firstOrCreate(
                ['default_for' => 'organizationManager'],
                ['name' => 'organizationManager', 'guard_name' => 'web']
            );

            $user->allotRole($role->default_for);

            $organization = Organization::create([
                'name_en' => $data['name'],
                'name_ar' => $data['name'],
                'img_url' => $img_url,
                'status' => $data['status'],
                'user_id' => $user->id,
            ]);

            $user->organization_id = $organization->id;
            $user->save();

            Mail::to($user->email)->queue(new OrganizationCreatedMail($organization, $user, $password));

            return $organization;
        });
    }

    public function updateOrganization(Organization $organization, array $data,?UploadedFile $file): Organization
    {
        $img_url = $file 
            ? $this->fileManager->update($file, $organization->img_url, 'organizations', 'organizations') 
            : $organization->img_url;

        return DB::transaction(function () use ($organization, $data, $img_url) {
            $user = $organization->user;

            $organization->update([
                'name_en' => $data['name'] ?? $organization->name_en,
                'name_ar' => $data['name'] ?? $organization->name_ar,
                'img_url' => $img_url,
                'status' => $data['status'] ?? $organization->status,
            ]);

            $user->fill([
                'first_name_en' => $data['firstName'] ?? $user->first_name_en,
                'last_name_en' => $data['lastName'] ?? $user->last_name_en,
                'first_name_ar' => $data['firstName'] ?? $user->first_name_ar,
                'last_name_ar' => $data['lastName'] ?? $user->last_name_ar,
                'gender' => $data['gender'] ?? $user->gender,
                'phone' => $data['phone'] ?? $user->phone,
                'country_code' => $data['countryCode'] ?? $user->country_code,
            ]);

            if (!empty($data['email']) && $data['email'] !== $user->email) {
                $user->email = $data['email'];
            }

            if (!empty($data['password'])) {
                $user->password = bcrypt($data['password']);
            }

            $user->save();

            return $organization->fresh(['user', 'cycles']); 
        });
    }

    public function deleteOrganization(Organization $organization): bool|string
    {
        $hasCycles = $organization->cycles()->exists();
        $hasCompanies = $organization->cycles->flatMap->companies->isNotEmpty();

        if ($hasCycles || $hasCompanies) {
            $organization->delete();

            Log::warning('Organization soft deleted due to active dependencies.', [
                'organization_id' => $organization->id,
                'has_cycles' => $hasCycles,
                'has_companies' => $hasCompanies,
                'performed_by' => auth()->id(),
            ]);

            return 'soft';
        }

        if ($organization->img_url && $organization->img_url !== '/images/background/logo.png') {
            $this->fileManager->delete($organization->img_url, 'organizations');
        }

        $organization->forceDelete();
        return true;
    }

    protected function uploadImage(?UploadedFile $file): string
    {
        if (!$file) {
            return '/images/background/logo.png';
        }
        return $this->fileManager->upload($file, 'organizations', 'organizations');
    }
}

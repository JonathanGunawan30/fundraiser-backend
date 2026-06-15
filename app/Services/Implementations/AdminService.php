<?php

namespace App\Services\Implementations;

use App\Models\Admin;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use App\Services\Interfaces\AdminServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminService implements AdminServiceInterface
{
    protected AdminRepositoryInterface $adminRepository;

    public function __construct(AdminRepositoryInterface $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllAdmins(int $perPage): LengthAwarePaginator
    {
        return $this->adminRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getAdminById(int $id): Admin
    {
        $admin = $this->adminRepository->findById($id);

        if (!$admin) {
            throw new ModelNotFoundException("Admin with ID {$id} not found.");
        }

        return $admin;
    }

    /**
     * @inheritDoc
     */
    public function searchAdmins(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->adminRepository->search($keyword, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function updateProfile(int $id, array $data): Admin
    {
        $admin = $this->getAdminById($id);

        if (isset($data['avatar'])) {
            // Delete old avatar if exists
            if ($admin->avatar_url) {
                $this->deleteFromR2($admin->avatar_url);
            }

            $path = $data['avatar']->store('avatars', 'r2');
            $data['avatar_url'] = Storage::disk('r2')->url($path);
            unset($data['avatar']);
        }

        return $this->adminRepository->update($admin, $data);
    }

    /**
     * @inheritDoc
     */
    public function updatePassword(int $id, string $currentPassword, string $newPassword): bool
    {
        $admin = $this->getAdminById($id);

        if (!Hash::check($currentPassword, $admin->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        $this->adminRepository->update($admin, [
            'password' => Hash::make($newPassword),
        ]);

        return true;
    }

    /**
     * Delete file from R2 disk.
     *
     * @param string $url
     * @return void
     */
    private function deleteFromR2(string $url): void
    {
        $baseUrl = Storage::disk('r2')->url('');
        $path = ltrim(str_replace($baseUrl, '', $url), '/');
        if ($path) {
            Storage::disk('r2')->delete($path);
        }
    }
}

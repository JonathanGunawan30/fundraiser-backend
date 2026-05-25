<?php

namespace App\Services\Implementations;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class UserService implements UserServiceInterface
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllUsers(int $perPage): LengthAwarePaginator
    {
        return $this->userRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getUserById(int $id): User
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new ModelNotFoundException("User with ID {$id} not found.");
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function searchUsers(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->userRepository->search($keyword, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function updateProfile(int $id, array $data): User
    {
        $user = $this->getUserById($id);

        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            // Delete old avatar
            if ($user->avatar_url) {
                $this->deleteFromR2($user->avatar_url);
            }

            $filename = Str::uuid() . '.' . $data['avatar']->getClientOriginalExtension();
            $path = $data['avatar']->storeAs('avatars', $filename, 'r2');
            $data['avatar_url'] = Storage::disk('r2')->url($path);
            unset($data['avatar']);
        }

        return $this->userRepository->update($id, $data);
    }

    /**
     * Delete file from R2.
     */
    protected function deleteFromR2(string $url): void
    {
        $baseUrl = Storage::disk('r2')->url('');
        $path = ltrim(str_replace($baseUrl, '', $url), '/');
        if ($path) {
            Storage::disk('r2')->delete($path);
        }
    }
}

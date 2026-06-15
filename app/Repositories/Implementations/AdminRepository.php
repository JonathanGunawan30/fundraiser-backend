<?php

namespace App\Repositories\Implementations;

use App\Models\Admin;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminRepository implements AdminRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator
    {
        return Admin::paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?Admin
    {
        return Admin::find($id);
    }

    /**
     * @inheritDoc
     */
    public function search(string $keyword, int $perPage): LengthAwarePaginator
    {
        return Admin::where('name', 'like', "%{$keyword}%")
            ->orWhere('email', 'like', "%{$keyword}%")
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function update(Admin $admin, array $data): Admin
    {
        $admin->fill($data);
        $admin->save();
        return $admin->fresh();
    }
}

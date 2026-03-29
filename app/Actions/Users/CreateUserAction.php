<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class CreateUserAction
{
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'role' => $data['role'] ?: null,
                'email' => $data['email'] ?: null,
                'phone' => $data['phone'] ?: null,
                'password' => Hash::make($data['password']),
            ]);

            if (!empty($data['permissions'])) {
                $user->syncPermissions($data['permissions']);
            }

            $user->places()->sync($data['place_ids'] ?? []);

            return $user;
        });
    }
}

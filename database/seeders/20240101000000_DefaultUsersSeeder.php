<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Database\Seeders\SeederInterface;
use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Services\Security\HashService;

class DefaultUsersSeeder implements SeederInterface
{
    private HashService $hashService;

    public function __construct()
    {
        $this->hashService = new HashService();
    }

    public function run(DatabaseConnectorInterface $connector): void
    {
        $users = [
            [
                'email' => 'superadmin@example.com',
                'password' => $this->hashService->make('superadmin123'),
                'name' => 'Super Admin',
                'role' => 'super_admin',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email' => 'admin@example.com',
                'password' => $this->hashService->make('admin123'),
                'name' => 'Admin',
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email' => 'user@example.com',
                'password' => $this->hashService->make('user123'),
                'name' => 'User',
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($users as $user) {
            $existing = $connector->findWhere('users', ['email' => $user['email']]);
            
            if ($existing === null) {
                $connector->create('users', $user);
            }
        }
    }
}

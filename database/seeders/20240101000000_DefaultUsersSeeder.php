<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Database\Seeders\SeederInterface;
use App\Repositories\Connectors\PropelConnector;
use App\Services\Security\HashService;

class DefaultUsersSeeder implements SeederInterface
{
    private HashService $hashService;

    public function __construct()
    {
        $this->hashService = new HashService();
    }

    public function run(PropelConnector $connector): void
    {
        $users = [
            [
                'email' => 'superadmin@example.com',
                'password' => $this->hashService->make('superadmin123'),
                'name' => 'Super Admin',
                'role' => 'super_admin',
            ],
            [
                'email' => 'admin@example.com',
                'password' => $this->hashService->make('admin123'),
                'name' => 'Admin',
                'role' => 'admin',
            ],
            [
                'email' => 'user@example.com',
                'password' => $this->hashService->make('user123'),
                'name' => 'User',
                'role' => 'user',
            ],
        ];

        foreach ($users as $userData) {
            $existing = $connector->findUserByEmail($userData['email']);
            
            if ($existing === null) {
                $connector->createUser($userData);
            } else {
                // Update existing user to ensure correct role and password
                $connector->updateUser($existing, $userData);
            }
        }
    }
}

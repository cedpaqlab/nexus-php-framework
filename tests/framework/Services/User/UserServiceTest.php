<?php

declare(strict_types=1);

namespace Tests\Framework\Services\User;

use Tests\Support\TestCase;
use App\Services\User\UserService;
use App\Repositories\User\UserRepository;
use App\Services\Security\HashService;
use App\Services\Security\Validator;

class UserServiceTest extends TestCase
{
    private UserService $service;
    private UserRepository $repository;
    private HashService $hashService;
    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = $this->createMock(UserRepository::class);
        $this->hashService = $this->createMock(HashService::class);
        $this->validator = $this->createMock(Validator::class);
        
        $this->service = new UserService(
            $this->repository,
            $this->hashService,
            $this->validator
        );
    }

    public function testCreateUserHashesPassword(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'user',
        ];

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $this->hashService->expects($this->once())
            ->method('make')
            ->with('password123')
            ->willReturn('hashed_password');

        $this->repository->expects($this->once())
            ->method('create')
            ->willReturn(1);

        $id = $this->service->create($data);
        
        $this->assertEquals(1, $id);
    }

    public function testUpdateUserUpdatesUser(): void
    {
        $existingUser = [
            'id' => 1,
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'role' => 'user',
        ];

        $this->repository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($existingUser);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $this->repository->expects($this->once())
            ->method('update')
            ->with(1, $this->anything())
            ->willReturn(1);

        $result = $this->service->update(1, ['name' => 'New Name']);
        
        $this->assertEquals(1, $result);
    }

    public function testDeleteUserDeletesUser(): void
    {
        $user = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com'];

        $this->repository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($user);

        $this->repository->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(1);

        $result = $this->service->delete(1);
        
        $this->assertEquals(1, $result);
    }

    public function testUpdateRoleUpdatesRole(): void
    {
        $user = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com', 'role' => 'user'];

        $this->repository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($user);

        $this->repository->expects($this->once())
            ->method('update')
            ->with(1, ['role' => 'admin'])
            ->willReturn(1);

        $result = $this->service->updateRole(1, 'admin');
        
        $this->assertEquals(1, $result);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Framework\Services\Auth;

use Tests\Support\TestCase;
use App\Services\Auth\AuthService;
use App\Repositories\User\UserRepository;
use App\Services\Security\HashService;
use App\Services\Session\SessionService;

class AuthServiceTest extends TestCase
{
    private AuthService $service;
    private UserRepository $repository;
    private HashService $hashService;
    private SessionService $session;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->repository = $this->createMock(UserRepository::class);
        $this->hashService = $this->createMock(HashService::class);
        $this->session = new SessionService();
        
        $this->service = new AuthService(
            $this->repository,
            $this->hashService,
            $this->session
        );
    }

    public function testAttemptReturnsFalseForInvalidEmail(): void
    {
        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with('invalid@example.com')
            ->willReturn(null);
        
        $result = $this->service->attempt('invalid@example.com', 'password');
        
        $this->assertFalse($result);
    }

    public function testAttemptReturnsFalseForInvalidPassword(): void
    {
        $user = [
            'id' => 1,
            'email' => 'test@example.com',
            'password' => 'hashed_password',
            'role' => 'user',
        ];
        
        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with('test@example.com')
            ->willReturn($user);
        
        $this->hashService->expects($this->once())
            ->method('verify')
            ->with('wrongpassword', 'hashed_password')
            ->willReturn(false);
        
        $result = $this->service->attempt('test@example.com', 'wrongpassword');
        
        $this->assertFalse($result);
    }

    public function testAttemptLogsInUserOnSuccess(): void
    {
        $user = [
            'id' => 1,
            'email' => 'test@example.com',
            'password' => 'hashed_password',
            'role' => 'user',
        ];
        
        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with('test@example.com')
            ->willReturn($user);
        
        $this->hashService->expects($this->once())
            ->method('verify')
            ->with('password123', 'hashed_password')
            ->willReturn(true);
        
        $result = $this->service->attempt('test@example.com', 'password123');
        
        $this->assertTrue($result);
        $this->assertTrue($this->session->has('user_id'));
        $this->assertEquals(1, $this->session->get('user_id'));
        $this->assertEquals('test@example.com', $this->session->get('user_email'));
    }

    public function testLogoutClearsSession(): void
    {
        $this->session->set('user_id', 1);
        $this->session->set('user_email', 'test@example.com');
        $this->session->set('user_role', 'user');
        
        $this->service->logout();
        
        $this->assertFalse($this->session->has('user_id'));
    }

    public function testIsSuperAdminReturnsTrueForSuperAdmin(): void
    {
        $this->session->set('user_id', 1);
        $this->session->set('user_role', 'super_admin');
        
        $this->assertTrue($this->service->isSuperAdmin());
    }

    public function testIsAdminReturnsTrueForAdmin(): void
    {
        $this->session->set('user_id', 1);
        $this->session->set('user_role', 'admin');
        
        $this->assertTrue($this->service->isAdmin());
    }
}

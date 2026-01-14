<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Repositories\User\UserRepository;
use App\Services\Security\HashService;
use App\Services\Session\SessionService;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private HashService $hashService,
        private SessionService $session
    ) {
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            return false;
        }

        if (!$this->hashService->verify($password, $user['password'])) {
            return false;
        }

        $this->login($user);

        return true;
    }

    public function login(array $user): void
    {
        $this->session->set('user_id', $user['id']);
        $this->session->set('user_email', $user['email']);
        $this->session->set('user_role', $user['role'] ?? 'user');
    }

    public function logout(): void
    {
        $this->session->flush();
    }

    public function user(): ?array
    {
        if (!$this->session->has('user_id')) {
            return null;
        }

        $userId = $this->session->get('user_id');
        return $this->userRepository->findById($userId);
    }

    public function check(): bool
    {
        return $this->session->has('user_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->session->get('user_role') === 'super_admin';
    }

    public function isAdmin(): bool
    {
        $role = $this->session->get('user_role');
        return in_array($role, ['admin', 'super_admin'], true);
    }
}

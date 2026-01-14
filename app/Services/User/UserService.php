<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Repositories\User\UserRepository;
use App\Services\Security\HashService;
use App\Services\Security\Validator;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private HashService $hashService,
        private Validator $validator
    ) {
    }

    public function create(array $data): int
    {
        $this->validateUserData($data, true);

        $data['password'] = $this->hashService->make($data['password']);
        $data['role'] = $data['role'] ?? 'user';
        $data['created_at'] = date('Y-m-d H:i:s');

        return $this->userRepository->create($data);
    }

    public function update(int $id, array $data): int
    {
        $existingUser = $this->userRepository->findById($id);
        if ($existingUser === null) {
            throw new \RuntimeException("User with ID {$id} not found");
        }

        $this->validateUserData($data, false, $existingUser);

        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = $this->hashService->make($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->userRepository->update($id, $data);
    }

    public function delete(int $id): int
    {
        $user = $this->userRepository->findById($id);
        if ($user === null) {
            throw new \RuntimeException("User with ID {$id} not found");
        }

        return $this->userRepository->delete($id);
    }

    public function updateRole(int $id, string $role): int
    {
        if (!in_array($role, ['user', 'admin', 'super_admin'], true)) {
            throw new \InvalidArgumentException("Invalid role: {$role}");
        }

        $user = $this->userRepository->findById($id);
        if ($user === null) {
            throw new \RuntimeException("User with ID {$id} not found");
        }

        return $this->userRepository->update($id, ['role' => $role]);
    }

    public function getAllUsers(array $filters = []): array
    {
        $conditions = [];
        if (isset($filters['role']) && $filters['role'] !== '') {
            $conditions['role'] = $filters['role'];
        }

        return $this->userRepository->findAll($conditions, ['created_at' => 'DESC']);
    }

    public function getUserById(int $id): ?array
    {
        return $this->userRepository->findById($id);
    }

    private function validateUserData(array $data, bool $isNew, ?array $existingUser = null): void
    {
        $rules = [
            'email' => 'required|email',
            'name' => 'required|string|min:2|max:100',
        ];

        if ($isNew) {
            $rules['password'] = 'required|string|min:8';
        } elseif (isset($data['password'])) {
            $rules['password'] = 'string|min:8';
        }

        if ($isNew || (isset($data['email']) && $data['email'] !== ($existingUser['email'] ?? null))) {
            $rules['email'] .= '|unique:users,email';
        }

        $errors = $this->validator->validate($data, $rules);

        if (!empty($errors)) {
            $errorMessages = [];
            foreach ($errors as $field => $fieldErrors) {
                $errorMessages[$field] = $fieldErrors[0] ?? 'Invalid value';
            }
            throw new \InvalidArgumentException('Validation failed: ' . json_encode($errorMessages));
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\View\ViewRenderer;
use App\Services\User\UserService;
use App\Services\Session\SessionService;

class AdminController
{
    public function __construct(
        private ViewRenderer $viewRenderer,
        private Response $response,
        private UserService $userService,
        private SessionService $session
    ) {
    }

    public function dashboard(Request $request): Response
    {
        $users = $this->userService->getAllUsers();
        $currentUser = [
            'id' => $this->session->get('user_id'),
            'email' => $this->session->get('user_email'),
            'role' => $this->session->get('user_role'),
        ];

        $html = $this->viewRenderer->render('admin/dashboard', [
            'users' => $users,
            'currentUser' => $currentUser,
            'stats' => [
                'total_users' => count($users),
                'admin_count' => count(array_filter($users, fn($u) => $u['role'] === 'admin')),
                'user_count' => count(array_filter($users, fn($u) => $u['role'] === 'user')),
            ],
        ]);

        return $this->response->html($html);
    }

    public function users(Request $request): Response
    {
        $filters = [
            'role' => $request->get('role', ''),
        ];

        $users = $this->userService->getAllUsers($filters);

        $html = $this->viewRenderer->render('admin/users/index', [
            'users' => $users,
            'filters' => $filters,
        ]);

        return $this->response->html($html);
    }

    public function createUser(Request $request): Response
    {
        if ($request->method() === 'POST') {
            try {
                $data = $request->all();
                $data['role'] = $data['role'] ?? 'user';
                $id = $this->userService->create($data);
                return $this->response->json(['success' => true, 'id' => $id], 201);
            } catch (\Exception $e) {
                return $this->response->json(['success' => false, 'error' => $e->getMessage()], 400);
            }
        }

        $html = $this->viewRenderer->render('admin/users/create');
        return $this->response->html($html);
    }

    public function editUser(Request $request, string $id): Response
    {
        $userId = (int) $id;
        $user = $this->userService->getUserById($userId);

        if ($user === null) {
            return $this->response->notFound('User not found');
        }

        if ($request->method() === 'PUT' || $request->method() === 'POST') {
            try {
                $data = $request->all();
                $this->userService->update($userId, $data);
                return $this->response->json(['success' => true]);
            } catch (\Exception $e) {
                return $this->response->json(['success' => false, 'error' => $e->getMessage()], 400);
            }
        }

        $html = $this->viewRenderer->render('admin/users/edit', ['user' => $user]);
        return $this->response->html($html);
    }

    public function deleteUser(Request $request, string $id): Response
    {
        $userId = (int) $id;

        try {
            $this->userService->delete($userId);
            return $this->response->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function updateRole(Request $request, string $id): Response
    {
        $userId = (int) $id;
        $role = $request->get('role', '');

        try {
            $this->userService->updateRole($userId, $role);
            return $this->response->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
}

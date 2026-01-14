<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\View\ViewRenderer;
use App\Services\User\UserService;
use App\Services\Session\SessionService;
use App\Services\Security\Validator;
use App\Http\Requests\Admin\CreateUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;

class AdminController
{
    public function __construct(
        private ViewRenderer $viewRenderer,
        private Response $response,
        private UserService $userService,
        private SessionService $session,
        private Validator $validator
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
        $role = $request->get('role', '');
        if ($role !== '' && !in_array($role, ['user', 'admin', 'super_admin'], true)) {
            $role = '';
        }
        
        $filters = [
            'role' => $role,
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
            $formRequest = new CreateUserRequest($request, $this->validator, $this->response);
            $validationResult = $formRequest->validate();
            
            if ($validationResult instanceof Response) {
                return $validationResult;
            }
            
            $data = $validationResult;
            $data['role'] = $data['role'] ?? 'user';
            
            try {
                $id = $this->userService->create($data);
                return $this->response->json(['success' => true, 'id' => $id], 201);
            } catch (\InvalidArgumentException $e) {
                $message = $e->getMessage();
                if (str_contains($message, 'json_encode')) {
                    $jsonString = str_replace('Validation failed: ', '', $message);
                    // PHP 8.3: Use json_validate() for efficient validation
                    if (json_validate($jsonString)) {
                        $decoded = json_decode($jsonString, true);
                        if (is_array($decoded)) {
                            return $this->response->json(['success' => false, 'errors' => $decoded], 422);
                        }
                    }
                }
                return $this->response->json(['success' => false, 'error' => $message], 400);
            } catch (\Exception $e) {
                return $this->response->json(['success' => false, 'error' => $e->getMessage()], 500);
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
            $formRequest = new UpdateUserRequest($request, $this->validator, $this->response);
            $validationResult = $formRequest->validate();
            
            if ($validationResult instanceof Response) {
                return $validationResult;
            }
            
            $data = $validationResult;
            
            try {
                $this->userService->update($userId, $data);
                return $this->response->json(['success' => true]);
            } catch (\InvalidArgumentException $e) {
                return $this->response->json(['success' => false, 'error' => 'Validation failed'], 400);
            } catch (\RuntimeException $e) {
                return $this->response->json(['success' => false, 'error' => 'User not found'], 404);
            } catch (\Exception $e) {
                return $this->response->json(['success' => false, 'error' => 'An error occurred'], 500);
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
        } catch (\RuntimeException $e) {
            return $this->response->json(['success' => false, 'error' => 'User not found'], 404);
        } catch (\Exception $e) {
            return $this->response->json(['success' => false, 'error' => 'An error occurred'], 500);
        }
    }

    public function updateRole(Request $request, string $id): Response
    {
        $userId = (int) $id;
        
        $formRequest = new UpdateRoleRequest($request, $this->validator, $this->response);
        $validationResult = $formRequest->validate();
        
        if ($validationResult instanceof Response) {
            return $validationResult;
        }
        
        $role = $validationResult['role'] ?? '';

        try {
            $this->userService->updateRole($userId, $role);
            return $this->response->json(['success' => true]);
        } catch (\InvalidArgumentException $e) {
            return $this->response->json(['success' => false, 'error' => 'Invalid role'], 400);
        } catch (\RuntimeException $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'Cannot change role of super_admin')) {
                return $this->response->json(['success' => false, 'error' => 'Cannot change role of super_admin user'], 403);
            }
            return $this->response->json(['success' => false, 'error' => 'User not found'], 404);
        } catch (\Exception $e) {
            return $this->response->json(['success' => false, 'error' => 'An error occurred'], 500);
        }
    }
}

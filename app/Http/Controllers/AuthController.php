<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\View\ViewRenderer;
use App\Services\Auth\AuthService;

class AuthController
{
    public function __construct(
        private ViewRenderer $viewRenderer,
        private Response $response,
        private AuthService $authService
    ) {
    }

    public function showLogin(Request $request): Response
    {
        if ($this->authService->check()) {
            return $this->redirectToDashboard();
        }

        $html = $this->viewRenderer->render('auth/login');
        return $this->response->html($html);
    }

    public function login(Request $request): Response
    {
        $email = $request->get('email', '');
        $password = $request->get('password', '');

        if (empty($email) || empty($password)) {
            return $this->response->json(['success' => false, 'error' => 'Email and password are required'], 400);
        }

        try {
            if (!$this->authService->attempt($email, $password)) {
                return $this->response->json(['success' => false, 'error' => 'Invalid credentials'], 401);
            }
        } catch (\Throwable $e) {
            $message = $this->isConnectionError($e)
                ? 'Database unavailable. Start MySQL and check .env (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD).'
                : 'Login temporarily unavailable. Please try again.';
            return $this->response->json(['success' => false, 'error' => $message], 503);
        }

        $redirectUrl = $this->authService->isSuperAdmin() ? '/admin' : '/dashboard';
        return $this->response->json(['success' => true, 'redirect' => $redirectUrl]);
    }

    private function isConnectionError(\Throwable $e): bool
    {
        $msg = strtolower($e->getMessage());
        if (str_contains($msg, 'unable to open connection') || str_contains($msg, 'connection refused')) {
            return true;
        }
        $prev = $e->getPrevious();
        return $prev !== null && $this->isConnectionError($prev);
    }

    public function logout(Request $request): Response
    {
        $this->authService->logout();
        return $this->response->redirect('/');
    }

    private function redirectToDashboard(): Response
    {
        if ($this->authService->isSuperAdmin()) {
            return $this->response->redirect('/admin');
        }

        return $this->response->redirect('/dashboard');
    }
}

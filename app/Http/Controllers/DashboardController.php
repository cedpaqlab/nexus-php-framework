<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\View\ViewRenderer;
use App\Services\Session\SessionService;

class DashboardController
{
    public function __construct(
        private ViewRenderer $viewRenderer,
        private Response $response,
        private SessionService $session
    ) {
    }

    public function index(Request $request): Response
    {
        $user = [
            'id' => $this->session->get('user_id'),
            'email' => $this->session->get('user_email'),
            'role' => $this->session->get('user_role'),
        ];

        $html = $this->viewRenderer->render('dashboard/index', [
            'user' => $user,
        ]);

        return $this->response->html($html);
    }
}

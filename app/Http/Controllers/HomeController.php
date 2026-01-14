<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\View\ViewRenderer;

class HomeController
{
    public function __construct(
        private ViewRenderer $viewRenderer,
        private Response $response
    ) {
    }

    public function index(Request $request): Response
    {
        $html = $this->viewRenderer->render('home');
        return $this->response->html($html);
    }
}

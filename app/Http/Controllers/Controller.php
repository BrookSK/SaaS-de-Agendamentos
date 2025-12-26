<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;

abstract class Controller
{
    protected function view(string $view, array $data = [], int $status = 200): Response
    {
        return Response::html(View::render($view, $data), $status);
    }

    protected function redirect(string $to): Response
    {
        return Response::redirect($to);
    }

    /** @param array<string,string> $params */
    protected function params(array $params): array
    {
        return $params;
    }
}

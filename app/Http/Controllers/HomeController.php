<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;

final class HomeController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        return $this->view('home/index', [
            'tenant' => Tenant::current(),
        ]);
    }
}

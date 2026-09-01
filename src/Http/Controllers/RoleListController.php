<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use InternetGuru\LaravelUser\Support\PermissionSummary;

class RoleListController extends Controller
{
    public function index(PermissionSummary $summary): View
    {
        return view('ig-common::layouts.base', [
            'view' => 'role-list',
            'prefix' => 'ig-user::',
            'props' => [
                'rolePolicy' => $summary->groupedByRole(),
                'summary' => $summary,
            ],
        ]);
    }
}

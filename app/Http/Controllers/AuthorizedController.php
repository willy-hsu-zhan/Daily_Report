<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthorizedController extends Controller
{
    public function index(Request $request)
    {
        return view('errors.unauthorized');
    }
}

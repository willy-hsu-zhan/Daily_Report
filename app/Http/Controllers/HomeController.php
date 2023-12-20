<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if( !is_null(Auth::user()) )
        {
            return view('livewire.components.home');
        }
        else
        {
            return $this->showSessionExpireAlert();
        }
    }

    public function showSessionExpireAlert()
    {
        $tag = 'sweetalert.session_expire';

        $data = [
            'title'   => config($tag . '.' . 'title'),
            'text'    => config($tag . '.' . 'text'),
            'icon'    => config($tag . '.' . 'icon'),
            'tag'     => Str::afterLast($tag, '.')
        ];

        return view('errors.unauthorized', ['data' => $data]);
    }
}

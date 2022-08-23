<?php

namespace MGK\Auth\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use MGK\Auth\Services\Auth;

class AuthController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function auth(Auth $auth)
    {
        return $auth->redirect();
    }

    public function callback(Auth $auth)
    {
        if (! $code = request('code')) {
            return redirect()
                ->route('login')
                ->with('error', 'Access denied. Please try again.');
        }

        $user = $auth
            ->createOrUpdateUserFromAuthorizationCode(
                request('code')
            );

        if (! $user) {
            return redirect()
                ->route('login')
                ->with('error', 'Access denied. Please try again.');
        }

        auth()->guard()->login($user);

        return redirect()->route('admin.home');
    }
}

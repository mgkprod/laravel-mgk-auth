<?php

namespace MGK\Auth\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use MGK\Auth\Models\UserAuthToken;
use MGK\Auth\Services\Auth;

class Authenticate implements AuthenticatesRequests
{
    protected $factory;
    protected $auth;

    public function __construct(Factory $factory, Auth $auth)
    {
        $this->factory = $factory;
        $this->auth = $auth;
    }

    public function handle($request, Closure $next)
    {
        $this->authenticate($request);

        $user = $this->factory->guard()->user();
        $this->factory->guard()->logout();

        if (! $this->isUserStillValid($user)) {
            return redirect($this->redirectTo($request));
        }

        $user->refresh();
        $this->factory->guard()->login($user);

        return $next($request);
    }

    protected function authenticate($request)
    {
        if ($this->factory->guard()->check()) {
            return;
        }

        $this->unauthenticated($request);
    }

    protected function isUserStillValid(Authenticatable $user): bool
    {
        if (! $token = UserAuthToken::latestForUser($user)) {
            return false;
        }

        if ($token->isExpired()) {
            return $this->auth->attemptToRefreshUser($user);
        }

        return true;
    }

    protected function unauthenticated($request)
    {
        throw new AuthenticationException(
            'Unauthenticated.',
            [null],
            $this->redirectTo($request)
        );
    }

    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}

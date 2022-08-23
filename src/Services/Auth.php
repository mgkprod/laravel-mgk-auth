<?php

namespace MGK\Auth\Services;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use MGK\Auth\Models\UserAuthToken;

class Auth
{
    protected $httpClient;

    public function __construct(
        protected string $host,
        protected string $clientId,
        protected string $clientSecret
    ) {
        $this->httpClient = Http::baseUrl($host);
        ! config('mgk-auth.verify_ssl') && $this->httpClient->withoutVerifying();
    }

    public function redirect()
    {
        return redirect($this->getAuthorizationUrl());
    }

    private function getAuthorizationUrl(): string
    {
        $state = Str::random(40);

        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => route('mgk-auth.callback'),
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);

        return $this->host . '/oauth/authorize?' . $query;
    }

    public function createOrUpdateUserFromAuthorizationCode(string $code): ?Authenticatable
    {
        $response = $this->httpClient->asForm()->post('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => route('mgk-auth.callback'),
            'code' => $code,
        ]);

        $tokenResponse = $response->json();

        if (isset($tokenResponse['error'])) {
            return null;
        }

        return $this->createOrUpdateUserFromToken($tokenResponse);
    }

    public function attemptToRefreshUser(Authenticatable $user): bool
    {
        $token = UserAuthToken::latestForUser($user);

        $response = $this->httpClient->asForm()->post('/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token->refresh_token,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => '',
        ]);

        $tokenResponse = $response->json();

        if (isset($tokenResponse['error'])) {
            return false;
        }

        $this->createOrUpdateUserFromToken($tokenResponse);

        return true;
    }

    public function createOrUpdateUserFromToken(array $tokenResponse): ?Authenticatable
    {
        $userData = $this->getUser($tokenResponse['access_token']);

        $user = User::firstOrCreate([
            'email' => $userData['email'],
        ], [
            'name' => $userData['name'],
            'abilities' => $userData['abilities'],
            'password' => 'NO_PASSWORD',
        ]);

        $user->name = $userData['name'];
        $user->abilities = $userData['abilities'];
        $user->save();

        UserAuthToken::query()
            ->where('user_id', $user->id)
            ->where('provider', 'mgk')
            ->delete();

        UserAuthToken::create([
            'user_id' => $user->id,
            'access_token' => $tokenResponse['access_token'],
            'refresh_token' => $tokenResponse['refresh_token'],
            'expires_at' => now()->addSeconds($tokenResponse['expires_in']),
            'provider' => 'mgk',
        ]);

        return $user;
    }

    private function getUser(string $accessToken): array
    {
        $response = $this->httpClient
            ->withToken($accessToken)
            ->get('/api/user');

        if (! $response->successful()) {
            throw new \Exception('Unable to get user data. The server responded with a status code of ' . $response->status() . '.');
        }

        return $response->json();
    }
}

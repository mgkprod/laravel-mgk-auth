<p><img src="./.github/lock.svg" alt="Auth" width="64px"></p>

# laravel-mgk-auth

![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/mgkprod/laravel-mgk-auth?label=version&style=flat-square)

## About

Log users using [MGK SSO server](https://auth.mgk.dev).

## Installation

After adding the [MGK Satis repository](https://composer.mgk.dev) to your composer.json, you can install the package with composer:

```bash
composer require mgkprod/mgk-auth
```

Your User class' database table should meet these criterias:

- `id` must be an char(24) type field (ulid)
- `abilities` must be a json type field. It will be used to grant abilities to the user.
- `avatar` must be a varchar(255) type field. It will contain the user's avatar URL.

## Usage

### Authentication

To authenticate users, this package comes with the `Authenticate` middleware. You can add it inside your `app/Http/Kernel.php` file.

```php
protected $routeMiddleware = [
    // ...
    'auth.mgk' => \MGK\Auth\Http\Middleware\Authenticate::class,
];
```

You can then protect your routes with the `auth.mgk` middleware.

```php
Route::group(['middleware' => ['auth.mgk']], function () {
    // This routes will need authentication.
});
```

This middleware will also take care of:

- refreshing the user's data (name, abilities, ...)
- refreshing the user's token if it's expired
- logging out the user if he is not authenticated anymore or the token has been revoked

### Permissions

First, you need to add the `HasAbilities` trait to your User model.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MGK\Auth\Models\Concerns\HasAbilities;

class User extends Model
{
    use HasAbilities;
}
```

Then you can use the built-in Gate facade to check if the user has the required abilities.

```php
use Illuminate\Support\Facades\Gate;

if (Gate::allows('posts.create')) {
    // The user has the ability to create posts.
}
```

You can also use the `hasAbility` method to check if the user has a specific permission.

```php
$user->hasAbility('posts.create');
```

## License

Copyright (c) 2022 Simon Rubuano (@mgkprod) and contributors

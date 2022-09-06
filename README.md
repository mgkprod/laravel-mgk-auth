<p><img src="./.github/lock.svg" alt="Auth" width="64px"></p>

# laravel-mgk-auth

![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/mgkprod/laravel-mgk-auth?label=version&style=flat-square)

## About

Log users using [MGK SSO server](https://auth.mgk.dev).

## Installation

After adding the [MGK Satis repository](https://composer.mgk.dev) to your composer.json, you can install the package with composer:

```bash
composer require mgkprod/laravel-mgk-auth
```

Your User class' database table should meet these criterias:

- `id` must be an char(24) type field (ulid)
- `avatar` must be a varchar(255) type field. It will contain the user's avatar URL.
- `abilities` must be a json type field. It will be used to grant abilities to the user.

Eg: 
```php
public function up()
{
    Schema::create('users', function (Blueprint $table) {
        // ...
        $table->char('id', 36)->primary();
        $table->string('avatar');
        $table->json('abilities')->nullable();
    });
}
```

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

#### Use permissions with Vue and Inertia.js

This package contains a javascript function that will check if the user has the required abilities.

First you will need to share the app name and the user abilities in your Inertia app.
To do this, expose `appName` and `authUser` within your `HandleInertiaRequests` middleware.

```php
// app/Http/Middleware/HandleInertiaRequests.php

public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'appName' => config('app.name'),
        'authUser' => function () use ($request) {
            if (! $request->user()) {
                return null;
            }

            return $request->user()->only('id', 'name', 'email', 'avatar', 'abilities');
        },

        // ...
    ]);
}
```
Then, in your `app.js` file, import the `can` function and bind it to Vue.

```js
import { can } from '../../vendor/mgkprod/laravel-mgk-auth/src/js/can';

// Vue 3.x
const app = createApp({});
app.config.globalProperties.can = can;

// Vue 2.x
Vue.prototype.can = can;
```

Finally, you can use the `can` function in your Vue components.

```html
<template>
    <div v-if="can('posts.create')">
        <button @click="createPost">Create post</button>
    </div>
</template>
```

## License

Copyright (c) 2022 Simon Rubuano (@mgkprod) and contributors

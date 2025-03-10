<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Access\Response;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define('update-post', function (User $user, Post $post) {
            return $user->id === $post->user_id  ? Response::allow()
                : Response::deny('You can not update.');
        });

        Gate::define('delete-post', function (User $user, Post $post) {
            return $user->id === $post->user_id ? Response::allow()
                : Response::deny('You can not delete.');
        });
    }
}

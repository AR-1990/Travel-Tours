<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider; 
use Illuminate\Support\Facades\Gate;
use App\Models\Chat\Conversation;
use App\Models\Users\User;
use App\Policies\ConversationPolicy;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    protected $policies = [
        Conversation::class => ConversationPolicy::class,
    ];

    public function boot()
    {
        // Define authorization logic here
        Gate::define('view-conversation', function (User $user, Conversation $conversation) {
            return $conversation->user_one_id === $user->id || $conversation->user_two_id === $user->id;
        });
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Domain\Repositories\BookRepository::class, \App\Infrastructure\Repositories\EloquentBookRepository::class);
        $this->app->bind(\App\Domain\Repositories\MemberRepository::class, \App\Infrastructure\Repositories\EloquentMemberRepository::class);
        $this->app->bind(\App\Domain\Repositories\UserRepository::class, \App\Infrastructure\Repositories\EloquentUserRepository::class);
        $this->app->bind(\App\Domain\Repositories\LoanRepository::class, \App\Infrastructure\Repositories\EloquentLoanRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

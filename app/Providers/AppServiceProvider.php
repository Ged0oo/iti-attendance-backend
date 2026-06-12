<?php
namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        \App\Models\AttendanceRecord::observe(\App\Observers\AttendanceRecordObserver::class);
        \App\Models\Grade::observe(\App\Observers\GradeObserver::class);

        // Tell Laravel's built-in ResetPassword notification to link
        // to the frontend instead of a Laravel web route.
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . '/reset-password'
            . '?token=' . $token
            . '&email=' . urlencode($notifiable->getEmailForPasswordReset());
        });
    }
}

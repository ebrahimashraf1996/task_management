<?php

namespace App\Providers;

use App\Models\Task;
use App\Observers\AuditObserver;
use App\Observers\TaskObserver;
use Illuminate\Support\Facades\Response;
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
        Task::observe(TaskObserver::class);
        Task::observe(AuditObserver::class);

        Response::macro('success', function ($data = [], $message = null) {
            return Response::json([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ]);
        });

        Response::macro('error', function ($data = [], $message = null, $code = 400) {
            return Response::json([
                'success' => false,
                'message' => $message,
                'data' => $data,
            ], $code);
        });
    }
}

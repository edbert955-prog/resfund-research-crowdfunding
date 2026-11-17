<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureDonor;
use App\Http\Middleware\EnsureResearcher;
use App\Http\Middleware\EnsureReviewer;
use App\Http\Middleware\EnsureAdministrator;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware role khusus untuk area dashboard Donatur.
        $middleware->alias([
            'donatur' => EnsureDonor::class,
            'peneliti' => EnsureResearcher::class,
            'reviewer' => EnsureReviewer::class,
            'administrator' => EnsureAdministrator::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Default Laravel exception handling.
    })
    ->create();

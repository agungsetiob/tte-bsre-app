<?php

use App\Support\Flash;
use Illuminate\Http\RedirectResponse;

if (! function_exists('flash_success')) {
    /**
     * Redirect back dengan pesan sukses.
     */
    function flash_success(string $message): RedirectResponse
    {
        return Flash::success($message);
    }
}

if (! function_exists('flash_error')) {
    /**
     * Redirect back dengan pesan error.
     */
    function flash_error(string $message): RedirectResponse
    {
        return Flash::error($message);
    }
}

if (! function_exists('flash_success_error')) {
    /**
     * Redirect back dengan pesan sukses dan error sekaligus.
     */
    function flash_success_error(?string $success, ?string $error): RedirectResponse
    {
        return Flash::successAndError($success, $error);
    }
}

if (! function_exists('flash_success_to')) {
    /**
     * Redirect ke route tertentu dengan pesan sukses.
     */
    function flash_success_to(string $route, string $message, array $parameters = []): RedirectResponse
    {
        return Flash::successTo($route, $message, $parameters);
    }
}

if (! function_exists('flash_error_to')) {
    /**
     * Redirect ke route tertentu dengan pesan error.
     */
    function flash_error_to(string $route, string $message, array $parameters = []): RedirectResponse
    {
        return Flash::errorTo($route, $message, $parameters);
    }
}
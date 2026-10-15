<?php

namespace App\Support;

use Illuminate\Http\RedirectResponse;

class Flash
{
    /**
     * Redirect back dengan pesan sukses.
     */
    public static function success(string $message): RedirectResponse
    {
        return back()->with('success', $message);
    }

    /**
     * Redirect back dengan pesan error.
     */
    public static function error(string $message): RedirectResponse
    {
        return back()->with('error', $message);
    }

    /**
     * Redirect back dengan sukses DAN error sekaligus.
     * Berguna untuk kasus "sebagian berhasil, sebagian gagal".
     */
    public static function successAndError(?string $success, ?string $error): RedirectResponse
    {
        $response = back();

        if ($success !== null && $success !== '') {
            $response->with('success', $success);
        }

        if ($error !== null && $error !== '') {
            $response->with('error', $error);
        }

        return $response;
    }

    /**
     * Redirect ke route tertentu dengan pesan sukses.
     */
    public static function successTo(string $route, string $message, array $parameters = []): RedirectResponse
    {
        return redirect()->route($route, $parameters)->with('success', $message);
    }

    /**
     * Redirect ke route tertentu dengan pesan error.
     */
    public static function errorTo(string $route, string $message, array $parameters = []): RedirectResponse
    {
        return redirect()->route($route, $parameters)->with('error', $message);
    }

    /**
     * Redirect ke route tertentu dengan sukses + error sekaligus.
     */
    public static function successAndErrorTo(
        string $route,
        ?string $success,
        ?string $error,
        array $parameters = []
    ): RedirectResponse {
        $response = redirect()->route($route, $parameters);

        if ($success !== null && $success !== '') {
            $response->with('success', $success);
        }

        if ($error !== null && $error !== '') {
            $response->with('error', $error);
        }

        return $response;
    }
}
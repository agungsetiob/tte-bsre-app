<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->is_active) {
            if ($request->user()->role !== 'admin') {
                if ($request->expectsJson()) {
                    abort(403, 'Akun Anda sedang menunggu persetujuan Admin.');
                }
                
                if ($request->route()->getName() === 'approval.notice' || $request->route()->getName() === 'logout') {
                    return $next($request);
                }

                return redirect()->route('approval.notice');
            }
        }

        return $next($request);
    }
}
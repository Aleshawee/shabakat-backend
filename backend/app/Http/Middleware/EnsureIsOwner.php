<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsOwner
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->isOwner()) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        return $next($request);
    }
}

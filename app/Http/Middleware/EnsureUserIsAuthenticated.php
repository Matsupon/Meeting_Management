<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAuthenticated
{
    /**
     * Handle an incoming request. Redirect to login or return 401 for API.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('user_id');
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $request->session()->flush();
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
                }
                return redirect()->route('login');
            }
            if (($user->is_active ?? true) === false) {
                $request->session()->flush();
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['status' => 'error', 'message' => 'Account deactivated'], 403);
                }
                return redirect()->route('login')->with('error', 'Your account has been deactivated.');
            }
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

        return redirect()->route('login');
    }
}

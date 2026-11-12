<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = $request->user();
        abort_if($customer === null, 403);
        $isActive = DB::table('users')->where('user_id', $customer->user_id)->value('is_active');
        if (! $isActive) {
            Auth::logout();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'This customer account has been deactivated.']);
        }

        return $next($request);
    }
}

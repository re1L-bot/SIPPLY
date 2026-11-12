<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user('admin');
        abort_if($admin === null, 403);
        $isActive = DB::table('admins')->where('admin_id', $admin->admin_id)->value('is_active');
        if (! $isActive) {
            Auth::guard('admin')->logout();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'This admin account has been deactivated.']);
        }

        return $next($request);
    }
}

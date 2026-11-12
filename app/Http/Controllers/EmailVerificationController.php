<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class EmailVerificationController extends Controller
{
    /**
     * Display the email verification notice.
     */
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->route('account');
        }

        return view('auth.verify-email');
    }

    /**
     * Mark the authenticated or linked user's email address as verified.
     */
    public function verify(Request $request, int|string $id, string $hash): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'The verification link is invalid or has expired. Please log in and request a new link.']);
        }

        /** @var User|null $user */
        $user = User::query()->find($id);

        if ($user === null) {
            return redirect()->route('login')
                ->withErrors(['email' => 'We could not find an account matching that verification link.']);
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            return redirect()->route('login')
                ->withErrors(['email' => 'The verification link is invalid.']);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        if ($request->user()?->user_id === $user->user_id) {
            return redirect()->route('account')
                ->with('status', 'Your email address has been verified! Welcome to SipPly.');
        }

        return redirect()->route('login')
            ->with('status', 'Your email address has been verified! You can now sign in.');
    }

    /**
     * Send a new email verification notification.
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('account')->with('status', 'Your email address is already verified.');
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            Log::error('Resending verification email failed: '.$e->getMessage(), [
                'user_id' => $user->user_id,
            ]);

            return back()->withErrors([
                'email' => 'We could not send the verification email right now. Please try again in a few moments.',
            ]);
        }

        return back()->with('status', 'verification-link-sent');
    }
}

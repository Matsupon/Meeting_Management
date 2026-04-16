<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function showLogin()
    {
        // Redirect to dashboard if already logged in
        if (session('user_id')) {
            return redirect()->route('dashboard');
        }
        $email = old('email', session('user_email', ''));
        return view('login', compact('email'));
    }

    public function handleLogin(Request $request)
    {
        $email = $request->input('email', '');
        $password = $request->input('password', '');

        if (!empty($email) && !empty($password)) {
            $user = User::where('email', $email)->first();
            if ($user && Hash::check($password, $user->password)) {
                if (($user->is_active ?? true) === false) {
                    return redirect()->route('login')
                        ->with('error', 'This account has been deactivated.')
                        ->withInput();
                }
                // Regenerate session ID to prevent session fixation attacks
                $request->session()->regenerate();
                session(['user_id' => $user->id, 'user_name' => $user->full_name ?? $user->name ?? 'User', 'user_email' => $user->email]);
                return redirect()->route('dashboard');
            }
            return redirect()->route('login')
                ->with('error', 'Invalid email or password.')
                ->withInput();
        }

        return redirect()->route('login')
            ->with('error', 'Please enter both email and password.')
            ->withInput();
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }
}

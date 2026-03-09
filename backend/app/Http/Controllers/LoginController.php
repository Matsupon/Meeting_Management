<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLogin()
    {
        // Redirect to dashboard if already logged in
        if (session('user_id')) {
            return redirect()->route('dashboard');
        }
        return view('login');
    }

    public function handleLogin(Request $request)
    {
        $username = $request->input('username', '');
        $password = $request->input('password', '');

        if (!empty($username) && !empty($password)) {
            // Simple mock login (same logic as original login.php)
            session(['user_id' => 1, 'user_name' => $username]);
            return redirect()->route('dashboard');
        }

        return redirect()->route('login')
            ->with('error', 'Please enter both username and password.');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }
}

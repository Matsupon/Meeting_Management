<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        // Guard: redirect to login if not authenticated
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        return view('dashboard', [
            'username'    => session('user_name', 'Admin'),
            'currentDate' => now()->format('l, F j, Y'),
        ]);
    }

    public function scheduleList()
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        return view('schedule-list', [
            'username'    => session('user_name', 'Admin'),
            'currentDate' => now()->format('l, F j, Y'),
        ]);
    }
}

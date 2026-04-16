<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        $user = User::find(session('user_id'));
        if (!$user) {
            return redirect()->route('login');
        }
        return view('dashboard', [
            'user' => $user,
            'currentDate' => now()->format('l, F j, Y'),
        ]);
    }

    public function scheduleList()
    {
        return view('schedule-list', [
            'user_name'   => session('user_name', 'User'),
            'currentDate' => now()->format('l, F j, Y'),
        ]);
    }

    public function exportSummary(Request $request)
    {
        try {
            $month = $request->input('month', 'all'); // 'all' or 0-11 (Jan-Dec)
            $year = $request->input('year', date('Y'));

            $query = Event::where('user_id', session('user_id'))->orderBy('date', 'asc');
            if ($month !== 'all') {
                $startDate = sprintf('%d-%02d-01', $year, $month + 1);
                $endDate = date('Y-m-t', strtotime($startDate));
                $query->whereBetween('date', [$startDate, $endDate]);
            } else {
                $query->whereYear('date', $year);
            }

            $filteredSchedules = [];
            $query->chunk(100, function ($events) use (&$filteredSchedules) {
                foreach ($events as $event) {
                    $filteredSchedules[] = [
                        'id' => $event->id,
                        'title' => $event->title,
                        'date' => $event->date,
                        'end_date' => $event->end_date,
                        'location' => $event->location,
                        'classification' => $event->classification,
                        'description' => $event->description,
                        'status' => $event->status,
                        'color' => $event->color,
                        'day_overrides' => $event->day_overrides,
                    ];
                }
            });

            // Log for debugging
            $count = $query->count();
            Log::info("Export Summary - Month: $month, Year: $year, Found: $count schedules");

            $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                           'July', 'August', 'September', 'October', 'November', 'December'];
            $reportTitle = $month === 'all' ? "Year $year" : "$monthNames[$month] $year";
            $filename = str_replace(' ', '_', strtolower($reportTitle)) . '_summary.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            return response()->stream(function () use ($query) {
                $handle = fopen('php://output', 'w');

                // Add UTF-8 BOM for Excel compatibility with special characters
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

                // Header row
                fputcsv($handle, ['Date', 'Title', 'Location', 'Classification', 'Status', 'Details']);

                $query->chunk(100, function ($events) use ($handle) {
                    foreach ($events as $event) {
                        fputcsv($handle, [
                            date('F j, Y', strtotime($event->date)),
                            $event->title,
                            $event->location ?? '',
                            $event->classification ?? '',
                            ucfirst($event->status),
                            $event->description ?? '',
                        ]);
                    }
                });

                fclose($handle);
            }, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Export Summary Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating summary: ' . $e->getMessage()
            ], 500);
        }
    }
}

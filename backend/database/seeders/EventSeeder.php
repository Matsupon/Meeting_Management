<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $events = [
            [
                'id'          => (string) Str::uuid(),
                'title'       => 'Faculty Orientation',
                'date'        => now()->format('Y-m-') . '10',
                'time'        => '08:00',
                'description' => 'Annual faculty orientation for the new semester.',
                'status'      => 'upcoming',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => (string) Str::uuid(),
                'title'       => 'Student Council Meeting',
                'date'        => now()->format('Y-m-') . '12',
                'time'        => '10:00',
                'description' => 'Monthly student council general assembly.',
                'status'      => 'upcoming',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => (string) Str::uuid(),
                'title'       => 'Thesis Defense Panel',
                'date'        => now()->format('Y-m-') . '14',
                'time'        => '13:00',
                'description' => 'Scheduled defense panel for graduating students.',
                'status'      => 'upcoming',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => (string) Str::uuid(),
                'title'       => 'Department Sync',
                'date'        => now()->format('Y-m-') . '05',
                'time'        => '09:30',
                'description' => 'Quick sync for all department heads.',
                'status'      => 'completed',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => (string) Str::uuid(),
                'title'       => 'UTPRAS Meeting',
                'date'        => now()->format('Y-m-') . '08',
                'time'        => '11:00',
                'description' => '',
                'status'      => 'cancelled',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        DB::table('events')->insert($events);
    }
}

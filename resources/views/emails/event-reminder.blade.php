<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #334155; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        h1 { color: #4f46e5; font-size: 24px; }
        .event { background: #f8fafc; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #4f46e5; }
        .event-title { font-weight: 700; font-size: 18px; color: #1e293b; }
        .event-date { color: #64748b; font-size: 14px; margin-top: 5px; }
        .event-location { color: #64748b; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upcoming Schedule Reminders</h1>
        <p>You have {{ count($events) }} upcoming schedule(s) in the next 1-3 days:</p>

        @foreach($events as $event)
        <div class="event">
            <div class="event-title">{{ $event['title'] }}</div>
            <div class="event-date">{{ \Carbon\Carbon::parse($event['date'])->format('l, F j, Y') }}</div>
            @if(!empty($event['location']))
            <div class="event-location">Location: {{ $event['location'] }}</div>
            @endif
        </div>
        @endforeach

        <p style="margin-top: 20px; color: #64748b; font-size: 14px;">
            Log in to your Schedule Management System to view details or make changes.
        </p>
    </div>
</body>
</html>

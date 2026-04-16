<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BumpAppVersion extends Command
{
    protected $signature = 'app:version-bump';

    protected $description = 'Increment the app version patch number (e.g. 1.0.0 -> 1.0.1)';

    public function handle(): int
    {
        $current = config('nativephp.version', '1.0.0');
        $parts = explode('.', $current);

        if (count($parts) < 3) {
            $parts = array_pad($parts, 3, 0);
        }

        $parts[2] = (int) ($parts[2] ?? 0) + 1;
        $newVersion = implode('.', $parts);

        $envPath = base_path('.env');
        $contents = file_exists($envPath) ? file_get_contents($envPath) : '';

        if (preg_match('/^NATIVEPHP_APP_VERSION=.*$/m', $contents)) {
            $contents = preg_replace('/^NATIVEPHP_APP_VERSION=.*$/m', "NATIVEPHP_APP_VERSION={$newVersion}", $contents);
        } else {
            $contents .= "\nNATIVEPHP_APP_VERSION={$newVersion}\n";
        }

        file_put_contents($envPath, $contents);

        $this->info("Version bumped: {$current} -> {$newVersion}");

        return Command::SUCCESS;
    }
}

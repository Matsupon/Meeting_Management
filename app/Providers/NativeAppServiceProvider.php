<?php

namespace App\Providers;

use App\Models\User;
use Native\Laravel\Facades\Window;
use Native\Laravel\Facades\Menu;
use Native\Laravel\Contracts\ProvidesPhpIni;
use Illuminate\Support\Facades\Artisan;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void    {
        // ONLY run the storage linker if we are NOT currently building the app
        if (!defined('NATIVEPHP_BUILDING')) {
            $this->ensureStorageLink();
        }

        if (app()->environment('production')) {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
        }

        Menu::create();

        Window::open()
            ->width(1200)
            ->height(800)
            ->rememberState();    }

    /**
     * Ensures that the public/storage link points to the correct location.
     * In NativePHP (both dev and production), storage lives in AppData/userData.
     * We must create a junction from public/storage to that path. Without this,
     * the prebuild symlink points to the build machine's path and breaks on installed apps.
     */
    protected function ensureStorageLink(): void
    {
        // Run when NativePHP is running (storage in userData) or local dev with AppData storage
        $isNativePHP = env('NATIVEPHP_RUNNING') === 'true';
        $isAppDataStorage = str_contains(storage_path(), 'AppData') || str_contains(storage_path(), 'userData');

        if (!$isNativePHP && !$isAppDataStorage) {
            return;
        }

        $publicStoragePath = public_path('storage');
        $targetPath = storage_path('app/public');

        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        if (PHP_OS_FAMILY === 'Windows') {
            // Check if junction exists and points to the correct target
            if (is_dir($publicStoragePath)) {
                $currentLink = @readlink($publicStoragePath);
                if ($currentLink !== $targetPath) {
                    shell_exec("cmd /c rmdir \"$publicStoragePath\" 2>NUL");
                    shell_exec("cmd /c mklink /J \"$publicStoragePath\" \"$targetPath\"");
                }
            }
            else {
                shell_exec("cmd /c mklink /J \"$publicStoragePath\" \"$targetPath\"");
            }
        }
        elseif (PHP_OS_FAMILY === 'Darwin' || PHP_OS_FAMILY === 'Linux') {
            if (!is_link($publicStoragePath) || readlink($publicStoragePath) !== $targetPath) {
                if (file_exists($publicStoragePath)) {
                    @unlink($publicStoragePath);
                }
                @symlink($targetPath, $publicStoragePath);
            }
        }
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}

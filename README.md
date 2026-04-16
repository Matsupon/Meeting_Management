# Meeting Management App

A modern, Laravel-powered meeting and schedule management application, built for the desktop using [NativePHP](https://nativephp.com/).

## Features

- **Calendar Dashboard**: Visual overview of all your meetings and events.
- **Event Management**: Easily create, edit, and delete events with a intuitive interface.
- **Recurring Events**: Support for daily, weekly, and monthly repetitions.
- **Attachments**: Attach relevant documents to your events.
- **Data Export**:
    - **CSV Summary**: Export your event logs for any month or year.
    - **iCal Feed**: Sync your events with external calendars via `/api/events/ical`.
- **System Tray Integration**: Quick access and notifications via NativePHP.
- **Profile Management**: Customize your user profile and manage security.

## Requirements

- **PHP 8.2+**
- **Composer**
- **Node.js & npm**
- **SQLite** (Default database for NativePHP)

## Getting Started

1. **Clone the repository**

2. **Install dependencies**
    ```bash
    composer install
    npm install
    ```

3. **Environment Setup**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Initialize Database**
    ```bash
    php artisan migrate
    php artisan db:seed
    ```

5. **Symlink Storage**
    ```bash
    php artisan storage:link
    ```

## Development

To run the application in a desktop window with hot-reloading:

```bash
php artisan native:serve
```

For frontend asset bundling during development:
```bash
npm run dev
```

## Production Build

To package the application for distribution:

```bash
php artisan native:build
```

## Technologies Used

- **Framework**: [Laravel 12+](https://laravel.com)
- **Desktop Runtime**: [NativePHP Electron](https://nativephp.com)
- **Styling**: [Tailwind CSS 4](https://tailwindcss.com)
- **Build Tool**: [Vite](https://vitejs.dev)
- **Database**: SQLite

## What is Project Tracker?

**Project Tracker** is a web-based dashboard designed to help users efficiently track the status of their projects. With features like push notification alerts for overdue tasks, detailed reporting tools, and exportable reports, Project Tracker simplifies project management and keeps you on top of deadlines.

**Key Features:**
- **Project Status Tracking:** Easily monitor the progress of all your projects in one centralized dashboard.
- **Push Notification Alerts:** Get instant notifications for tasks that have missed their deadlines.
- **Report Generation:** Create detailed reports on project progress and task statuses with a few clicks.
- **Export Reports:** Export project reports in various formats (e.g., EXCEL, CSV) for sharing and record-keeping.
- **User-Friendly Interface:** A clean and intuitive design ensures seamless navigation and usability.
- **Secure User Management:** Manage user accounts with role-based permissions for safe and controlled access.
- **Deadline Reminders:** Stay on schedule with automated alerts and reminders for upcoming deadlines.

**Why Choose Project Tracker?**
- **Stay Organized and On Track:** With its intuitive dashboard and real-time updates, Project Tracker helps you stay organized and ensures no task or deadline is overlooked.
- **Never Miss a Deadline:** Push notification alerts keep you informed about overdue tasks and deadlines, so you can take timely action.
- **Comprehensive Reporting:** Generate detailed reports to analyze your project progress, identify bottlenecks, and make data-driven decisions.
- **Effortless Report Sharing:** Export reports in user-friendly formats like PDF or CSV for easy sharing with team members or stakeholders.
- **User-Centric Design:** The clean, responsive interface ensures an excellent user experience on both desktop and mobile devices.
- **Customizable and Scalable:** Designed to adapt to projects of any size, whether you're an individual, a small team, or an entire organization.
- **Boost Productivity:** Stay focused on your goals with streamlined project tracking, task prioritization, and automated reminders.
- **Secure and Reliable:** Role-based access control ensures that your project data is secure and only accessible by authorized users.

## Table of Contents
[Installation](#installation)<br/>
[Setup Local Environment](#environment)<br/>
[Database](#database)<br/>
[Create Administrator Account](#create-admin-account)<br/>
[Install Filament Shield](#install-filament-shield)<br/>
[Support](#support)

<a name="installation"></a>
## Installation
Install the `dependencies` by running:

```bash
composer install
```

<a name="environment"></a>
## Setup Local Environment
Generate a new `.env` file by running:

```bash
cp .env.example .env
```

Configure your `.env` file:

```bash
APP_URL=https://project-tracker.dev

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

<a name="database"></a>
## Database
Assuming that you have already created an empty database, run this command to migrate the database tables:

```bash
php artisan migrate:fresh --seed
```

<a name="create-admin-account"></a>
## Create Administrator Account
In order to create an administrator account, run this command:

```bash
php artisan make:filament-user
```

<a name="install-filament-shield"></a>
## Install Filament Shield
In order to install [filament shield](https://filamentphp.com/plugins/bezhansalleh-shield), run this command:

```bash
php artisan shield:setup --fresh
```

Next, register the plugin for your panel:
```bash
php artisan shield:install app
```

Then, generate permissions/policies:
```bash
php artisan shield:generate --all
```

Finally, choose the super admin from the list of users:
```bash
php artisan shield:super-admin
```

<a name="support"></a>
## Support
This project was generated with [Laravel](https://laravel.com/) and [Filament](https://filamentphp.com).

For support, email jeddsaliba@gmail.com.
# Pricetag Cron Jobs

This directory contains the cron scheduler for Pricetag.

## Installation

Add the following line to your server's crontab (`crontab -e`):

```bash
* * * * * cd /path/to/pricetag && php cron/scheduler.php >> storage/logs/cron.log 2>&1
```

Replace `/path/to/pricetag` with the actual path to your installation.

## Scheduled Tasks

The scheduler runs the following tasks automatically:

| Task | Frequency | Description |
|------|-----------|-------------|
| `cache:gc` | Every hour | Clear expired cache entries |
| `sessions:cleanup` | Every 6 hours | Remove expired session files |
| `tokens:cleanup` | Daily at 2:00 AM | Remove expired password reset and email verification tokens |
| `sitemap:generate` | Daily at 3:00 AM | Regenerate sitemap.xml |
| `cart:reminders` | Every 4 hours | Send abandoned cart email reminders |
| `stock:sync` | Every 2 hours | Sync inventory from vendor APIs |
| `logs:cleanup` | Weekly (Sunday 4 AM) | Archive old log files |
| `db:optimize` | Weekly (Sunday 5 AM) | Optimize database tables |
| `stock:alerts` | Daily at 8:00 AM | Send low stock alerts to admins |
| `payments:process` | Every 15 minutes | Verify pending payment statuses |
| `health:check` | Every 5 minutes | Run system health checks |

## Manual Execution

Run the scheduler manually for testing:

```bash
php cron/scheduler.php
```

## Logs

Cron logs are stored in `storage/logs/cron.log`

Task execution history is stored in the `scheduled_task_logs` database table.

## Adding New Tasks

Edit `app/Services/Scheduler.php` and add tasks in the `registerTasks()` method:

```php
$this->schedule('my:task', function () {
    // Task logic here
    return "Task completed";
})->daily();
```

Available scheduling methods:
- `everyMinute()`
- `everyMinutes(int $minutes)`
- `hourly()`
- `everyHours(int $hours)`
- `daily()`
- `dailyAt(string $time)` - e.g., `'08:00'`
- `weeklyOn(int $day, string $time)` - day 0 = Sunday
- `monthlyOn(int $day, string $time)`

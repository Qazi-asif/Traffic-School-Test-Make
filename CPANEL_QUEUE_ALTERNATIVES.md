# cPanel Queue Alternatives - Complete Guide

## Problem

Shared hosting (cPanel) doesn't allow running `php artisan queue:work` continuously. You need alternative solutions to process queued jobs.

---

## ✅ Solution 1: Cron Job (RECOMMENDED for cPanel)

### How It Works
Run a cron job every minute to process pending queue jobs.

### Setup in cPanel

1. **Go to cPanel → Cron Jobs**

2. **Add New Cron Job:**
   - **Minute**: `*` (every minute)
   - **Hour**: `*`
   - **Day**: `*`
   - **Month**: `*`
   - **Weekday**: `*`
   - **Command**:
   ```bash
   cd /home/username/public_html && /usr/bin/php artisan queue:work --stop-when-empty --max-time=50
   ```

### Explanation
- `--stop-when-empty`: Exits when no jobs left
- `--max-time=50`: Runs for max 50 seconds (safe for 1-minute cron)
- Cron runs every minute, processes jobs, then exits
- Next cron cycle picks up new jobs

### Full Command Examples

**For cPanel:**
```bash
cd /home/username/public_html && /usr/bin/php artisan queue:work --stop-when-empty --max-time=50 >> /dev/null 2>&1
```

**With logging:**
```bash
cd /home/username/public_html && /usr/bin/php artisan queue:work --stop-when-empty --max-time=50 >> storage/logs/queue.log 2>&1
```

---

## ✅ Solution 2: Laravel Scheduler + Cron

### How It Works
Use Laravel's built-in scheduler to process jobs every minute.

### Step 1: Update Kernel.php

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Process queue jobs every minute
    $schedule->command('queue:work --stop-when-empty --max-time=50')
        ->everyMinute()
        ->withoutOverlapping();
}
```

### Step 2: Setup Single Cron Job

In cPanel, add ONE cron job:
```bash
* * * * * cd /home/username/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

This single cron runs Laravel's scheduler, which handles all scheduled tasks including queue processing.

---

## ✅ Solution 3: Sync Queue (No Queue Worker Needed)

### How It Works
Process jobs immediately without queuing (synchronous).

### Configuration

Change in `.env`:
```env
QUEUE_CONNECTION=sync
```

### Pros
- ✅ No cron job needed
- ✅ Works on any hosting
- ✅ Simple setup

### Cons
- ❌ Slower user experience (waits for job to complete)
- ❌ No retry logic
- ❌ Can timeout on long jobs

### When to Use
- Low traffic sites
- Non-critical jobs
- Shared hosting with no cron access

---

## ✅ Solution 4: Database Queue + Scheduled Command

### How It Works
Store jobs in database, process them via scheduled command.

### Step 1: Ensure Database Queue

In `.env`:
```env
QUEUE_CONNECTION=database
```

### Step 2: Create Custom Command

File: `app/Console/Commands/ProcessQueueJobs.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ProcessQueueJobs extends Command
{
    protected $signature = 'queue:process-batch';
    protected $description = 'Process a batch of queue jobs';

    public function handle(): int
    {
        $this->info('Processing queue jobs...');
        
        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--max-time' => 50,
            '--tries' => 3,
        ]);
        
        $this->info('Queue processing complete');
        
        return 0;
    }
}
```

### Step 3: Add to Scheduler

In `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('queue:process-batch')
        ->everyMinute()
        ->withoutOverlapping();
}
```

### Step 4: Setup Cron
```bash
* * * * * cd /home/username/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

---

## ✅ Solution 5: Hybrid Approach (BEST for Production)

### How It Works
Combine immediate processing for critical jobs with queue for non-critical.

### Configuration

1. **Critical Jobs (Immediate)**
   - Payment processing
   - User registration
   - Login/authentication

2. **Non-Critical Jobs (Queued)**
   - State transmissions
   - Email notifications
   - Report generation

### Implementation

**Option A: Conditional Dispatch**
```php
// In your code
if (config('queue.force_sync_critical')) {
    // Process immediately
    (new SendCaliforniaTransmissionJob($id))->handle(app(CaliforniaTvccService::class));
} else {
    // Queue normally
    SendCaliforniaTransmissionJob::dispatch($id);
}
```

**Option B: Multiple Queue Connections**

In `config/queue.php`:
```php
'connections' => [
    'sync' => [
        'driver' => 'sync',
    ],
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
    ],
],
```

Dispatch to specific queue:
```php
SendCaliforniaTransmissionJob::dispatch($id)->onQueue('default');
```

---

## 🎯 RECOMMENDED SOLUTION for cPanel

### Use Laravel Scheduler + Single Cron Job

This is the cleanest and most maintainable approach:

### Step 1: Update `app/Console/Kernel.php`

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Process queue jobs every minute
        $schedule->command('queue:work --stop-when-empty --max-time=50 --tries=3')
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/queue-cron.log'));

        // Send pending state transmissions every 5 minutes
        $schedule->command('transmissions:send FL')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule->command('transmissions:send CA')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        // Clean old failed jobs weekly
        $schedule->command('queue:prune-failed --hours=168')
            ->weekly();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
```

### Step 2: Add ONE Cron Job in cPanel

```bash
* * * * * cd /home/username/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Replace:**
- `/home/username/public_html` with your actual path
- `/usr/bin/php` with your PHP path (check with `which php` in SSH)

### Step 3: Verify PHP Path

In cPanel Terminal or SSH:
```bash
which php
# or
which php8.2
```

Common paths:
- `/usr/bin/php`
- `/usr/local/bin/php`
- `/opt/cpanel/ea-php82/root/usr/bin/php`

---

## 🔧 Alternative: Process Jobs on Web Request

### How It Works
Process a few queue jobs on each web request (not recommended for production).

### Implementation

Create middleware: `app/Http/Middleware/ProcessQueueJobs.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Artisan;

class ProcessQueueJobs
{
    public function handle($request, Closure $next)
    {
        // Process up to 3 jobs per request
        Artisan::call('queue:work', [
            '--once' => true,
            '--max-jobs' => 3,
        ]);

        return $next($request);
    }
}
```

Add to `app/Http/Kernel.php`:
```php
protected $middleware = [
    // ... other middleware
    \App\Http\Middleware\ProcessQueueJobs::class,
];
```

**⚠️ Warning**: This slows down every request. Only use as last resort.

---

## 📋 cPanel Setup Checklist

### ✅ Recommended Setup (Scheduler + Cron)

- [ ] Update `app/Console/Kernel.php` with schedule
- [ ] Find PHP path: `which php`
- [ ] Add cron job in cPanel
- [ ] Test: `php artisan schedule:run`
- [ ] Monitor: `storage/logs/queue-cron.log`
- [ ] Verify jobs are processing

### Cron Job Template

```bash
# Every minute - Laravel Scheduler
* * * * * cd /home/USERNAME/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1

# Or with logging
* * * * * cd /home/USERNAME/public_html && /usr/bin/php artisan schedule:run >> /home/USERNAME/public_html/storage/logs/cron.log 2>&1
```

---

## 🧪 Testing

### Test Scheduler Locally
```bash
php artisan schedule:run
```

### Test Queue Processing
```bash
php artisan queue:work --stop-when-empty
```

### Check Scheduled Tasks
```bash
php artisan schedule:list
```

### Monitor Logs
```bash
tail -f storage/logs/queue-cron.log
tail -f storage/logs/laravel.log
```

---

## 🚨 Troubleshooting

### Issue: Cron not running
**Check:**
- PHP path is correct
- Directory path is correct
- File permissions (755 for artisan)
- Cron is enabled in cPanel

**Test manually:**
```bash
cd /home/username/public_html && /usr/bin/php artisan schedule:run
```

### Issue: Jobs not processing
**Check:**
- `QUEUE_CONNECTION=database` in .env
- Jobs table exists: `php artisan queue:table`
- Cron is running: Check cPanel cron logs
- Laravel logs: `storage/logs/laravel.log`

### Issue: Jobs timing out
**Solution:**
- Increase `--max-time` value
- Split large jobs into smaller chunks
- Use `--tries=3` for retries

### Issue: Overlapping jobs
**Solution:**
- Use `->withoutOverlapping()` in scheduler
- Add `--max-time=50` to prevent overlap

---

## 📊 Performance Comparison

| Method | Speed | Reliability | cPanel Compatible | Recommended |
|--------|-------|-------------|-------------------|-------------|
| Cron + Scheduler | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ✅ | ✅ YES |
| Direct Cron | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ✅ | ✅ YES |
| Sync Queue | ⭐⭐ | ⭐⭐⭐ | ✅ | ⚠️ Low traffic only |
| Web Request | ⭐ | ⭐⭐ | ✅ | ❌ Not recommended |
| Supervisor | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ❌ | VPS/Dedicated only |

---

## 🎯 Final Recommendation for Your System

### Use Laravel Scheduler with Cron

**Why:**
- ✅ Works perfectly on cPanel
- ✅ One cron job manages everything
- ✅ Easy to maintain
- ✅ Handles overlapping
- ✅ Built-in logging
- ✅ Can schedule multiple tasks

**Setup Time:** 5 minutes

**Reliability:** Excellent

---

## 📝 Implementation Steps

### 1. Update Kernel.php (Already done below)

### 2. Add Cron Job in cPanel
- Login to cPanel
- Go to "Cron Jobs"
- Add new cron job with command above
- Save

### 3. Test
```bash
# SSH into your server
php artisan schedule:run

# Check if jobs processed
php artisan queue:failed
```

### 4. Monitor
```bash
# Check cron log
tail -f storage/logs/queue-cron.log

# Check Laravel log
tail -f storage/logs/laravel.log
```

---

## 🔄 What Gets Processed

With the scheduler setup:
- ✅ Florida transmissions (every 5 minutes)
- ✅ California transmissions (every 5 minutes)
- ✅ Email notifications (immediate via queue)
- ✅ Certificate generation (immediate via queue)
- ✅ Payment processing (immediate via queue)
- ✅ Failed job cleanup (weekly)

---

## 📞 Support

If cron jobs don't work:
1. Contact cPanel support for PHP path
2. Check cPanel cron job logs
3. Verify file permissions
4. Test command manually via SSH

---

**Recommended**: Use Laravel Scheduler + Single Cron Job
**Fallback**: Use Sync Queue (QUEUE_CONNECTION=sync)

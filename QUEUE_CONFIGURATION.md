# Queue Configuration for State Transmissions

## Environment Configuration

Add these variables to your `.env` file:

```env
# Queue Configuration
QUEUE_CONNECTION=database
# For production, consider Redis:
# QUEUE_CONNECTION=redis

# Florida API Configuration
FLORIDA_API_URL=https://api.flhsmv.gov/dicds/transmissions
FLORIDA_API_KEY=your_api_key_here
FLORIDA_USERNAME=your_username_here
FLORIDA_PASSWORD=your_password_here
FLORIDA_SCHOOL_ID=your_school_id_here
```

## Running Queue Workers

### Local Development

```bash
# Run queue worker
php artisan queue:work

# Run with specific options
php artisan queue:work --tries=3 --timeout=90

# Run queue worker for specific queue
php artisan queue:work --queue=state-transmissions,default
```

### Production

Use a process manager like Supervisor to keep queue workers running:

**supervisor.conf:**
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
stopwaitsecs=3600
```

## Queue Management Commands

```bash
# View failed jobs
php artisan queue:failed

# Retry a specific failed job
php artisan queue:retry {job-id}

# Retry all failed jobs
php artisan queue:retry all

# Delete a failed job
php artisan queue:forget {job-id}

# Clear all failed jobs
php artisan queue:flush

# Restart queue workers (after code changes)
php artisan queue:restart

# Monitor queue in real-time
php artisan queue:monitor database --max=100
```

## Redis Configuration (Recommended for Production)

Install Redis and configure:

```bash
composer require predis/predis
```

Update `.env`:
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Laravel Horizon (Optional - Redis Only)

For advanced queue monitoring with Redis:

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

Start Horizon:
```bash
php artisan horizon
```

Access dashboard at: `http://your-app.test/horizon`

## Monitoring Failed Jobs

Check failed jobs regularly:

```bash
# List all failed jobs
php artisan queue:failed

# View specific job details
php artisan tinker
>>> \DB::table('failed_jobs')->latest()->first()
```

## Testing Queue Jobs

```bash
# Run jobs synchronously in testing
# In .env.testing:
QUEUE_CONNECTION=sync

# Or test with actual queue
php artisan test --filter=StateTransmissionTest
```

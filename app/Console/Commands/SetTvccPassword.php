<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetTvccPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tvcc:password {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the TVCC API password';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $password = $this->argument('password');

        DB::table('tvcc_passwords')->insert([
            'password' => $password,
            'updated_at' => now(),
        ]);

        $this->info('TVCC password has been set successfully.');
    }
}
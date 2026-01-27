<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\TestFloridaTransmissionsSeeder;

class GenerateTestTransmissions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'transmissions:generate-test {count=5 : Number of test transmissions to create}';

    /**
     * The console command description.
     */
    protected $description = 'Generate test Florida transmissions for testing the admin interface';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');
        
        $this->info("🚀 Generating {$count} test Florida transmissions...");
        
        // Run the seeder
        $seeder = new TestFloridaTransmissionsSeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->newLine();
        $this->info('✅ Test transmissions generated successfully!');
        $this->info('🌐 View them at: http://127.0.0.1:8000/admin/state-transmissions');
        $this->newLine();
        
        $this->comment('💡 You can now test:');
        $this->line('   • Theme switcher (light/dark mode)');
        $this->line('   • Filter functionality');
        $this->line('   • Pagination');
        $this->line('   • Status badges and actions');
        $this->line('   • Detailed transmission view');
        
        return 0;
    }
}
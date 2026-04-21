<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeployAndServe extends Command
{
    protected $signature = 'deploy:serve {--port=8000 : The port to serve on}';
    protected $description = 'Deploy (migrate) and serve the application';

    public function handle()
    {
        $this->info('🚀 Deploying application...');

        // Wait for database connection
        $this->waitForDatabase();

        // Run migrations (only pending migrations, never fresh in production)
        $this->info('🗄️  Running migrations...');
        $this->call('migrate', ['--force' => true, '--no-interaction' => true]);

        // Cache configuration
        $this->info('⚡ Optimizing...');
        $this->call('optimize:clear');
        $this->call('optimize');

        // Start server
        $port = $this->option('port');
        $this->info("🌐 Starting server on 0.0.0.0:{$port}");
        $this->call('serve', [
            '--host' => '0.0.0.0',
            '--port' => $port,
        ]);
    }

    private function waitForDatabase()
    {
        $attempts = 0;
        $maxAttempts = 30;

        while ($attempts < $maxAttempts) {
            try {
                DB::connection()->getPdo();
                $this->info('✅ Database connected');
                return;
            } catch (\Exception $e) {
                $attempts++;
                $this->warn("⏳ Waiting for database... (attempt {$attempts}/{$maxAttempts})");
                sleep(2);
            }
        }

        $this->error('❌ Could not connect to database');
        exit(1);
    }
}

<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Admin\CronService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PipraPayCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'piprapay:cron {token?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run PipraPay periodic tasks (Currency updates, Webhook retries, etc.)';

    /**
     * Execute the console command.
     */
    public function handle(CronService $cronService): int
    {
        $this->info('Starting PipraPay Cron...');
        
        $token = $this->argument('token');
        
        // If run via CLI, we might want to bypass token check or use a system token
        // For now, we'll try to use the token if provided, otherwise the service might reject it if it strictly enforces it.
        // We can modify the service to allow null token if run via CLI.
        
        $result = $cronService->handle($token ?? '');
        
        if (($result['status'] ?? 'false') === 'true') {
            $this->info($result['message'] ?? 'Success');
            return Command::SUCCESS;
        }

        $this->error($result['message'] ?? 'Failed');
        Log::warning('PipraPay Cron Command Failed: ' . ($result['message'] ?? 'Unknown error'));
        return Command::FAILURE;
    }
}

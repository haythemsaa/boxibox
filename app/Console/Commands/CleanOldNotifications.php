<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:clean {--days=90 : Number of days to keep notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoie les notifications lues de plus de X jours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $date = Carbon::now()->subDays($days);

        $this->info("Nettoyage des notifications lues de plus de {$days} jours...");

        $deleted = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('created_at', '<', $date)
            ->delete();

        $this->info("✓ {$deleted} notification(s) supprimée(s)");

        return Command::SUCCESS;
    }
}

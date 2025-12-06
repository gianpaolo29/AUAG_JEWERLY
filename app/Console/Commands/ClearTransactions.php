<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Repair;
use App\Models\PawnItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:auag-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete ALL users, pawn items, repairs, transactions, transaction items, and payments. USE WITH CAUTION.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->warn('This will DELETE ALL:');
        $this->warn('- Users');
        $this->warn('- Pawn items');
        $this->warn('- Repairs');
        $this->warn('- Transactions');
        $this->warn('- Transaction items');
        $this->newLine();

        if (! $this->confirm('Are you sure you want to continue? This cannot be undone.')) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table((new TransactionItem)->getTable())->truncate();
        DB::table((new Transaction)->getTable())->truncate();
        DB::table((new Repair)->getTable())->truncate();
        DB::table((new PawnItem)->getTable())->truncate();
        DB::table((new User)->getTable())->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('All specified AUAG data has been cleared successfully.');

        return self::SUCCESS;
    }
}

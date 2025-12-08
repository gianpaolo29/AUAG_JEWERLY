<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Repair;
use App\Models\PawnItem;
use Illuminate\Console\Command;

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
        // Order matters if you have foreign keys (delete children first)
        TransactionItem::query()->delete();
        Transaction::query()->delete();
        Repair::query()->delete();
        PawnItem::query()->delete();
        User::query()->delete();

        $this->info('All specified AUAG data has been cleared successfully.');

        return self::SUCCESS;
    }
}

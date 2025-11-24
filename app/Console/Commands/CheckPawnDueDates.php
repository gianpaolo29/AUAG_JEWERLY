<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PawnItem;
use App\Models\User;
use App\Notifications\PawnDueDateReminderNotification;
use Carbon\Carbon;

class CheckPawnDueDates extends Command
{
    protected $signature = 'pawn:check-due-dates';
    protected $description = 'Send admin notifications when pawn due dates are near';

    public function handle()
    {
        $from = Carbon::today();
        $to   = Carbon::today()->addDays(3); // next 3 days

        $items = PawnItem::where('status', 'active')
            ->whereBetween('due_date', [$from, $to])
            ->get();

        if ($items->isEmpty()) {
            $this->info('No pawn items nearing due date.');
            return Command::SUCCESS;
        }

        $admins = User::where('role', 'admin')->get();

        foreach ($items as $item) {
            foreach ($admins as $admin) {
                $admin->notify(new PawnDueDateReminderNotification($item));
            }
        }

        $this->info('Pawn due date reminders sent.');
        return Command::SUCCESS;
    }
}

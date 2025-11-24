<?php

namespace App\Console\Commands;

use App\Models\PawnItem;
use App\Models\User;
use App\Notifications\PawnDueDateReminderNotification;
use Illuminate\Console\Command;

class CheckPawnDueDates extends Command
{
    protected $signature = 'pawn:check-due-dates';

    protected $description = 'Check for pawn items with approaching due dates and send notifications';

    public function handle()
    {
        $admin = User::where('role', 'admin')->first();

        if (! $admin) {
            $this->error('No admin user found!');

            return;
        }

        // Get active pawn items with due dates in the next 7 days or overdue
        $pawnItems = PawnItem::where('status', 'active')
            ->whereDate('due_date', '<=', now()->addDays(7))
            ->get();

        $notifiedCount = 0;

        foreach ($pawnItems as $pawnItem) {
            $daysLeft = now()->diffInDays($pawnItem->due_date, false);

            // Only notify for items due in 7 days or less (including overdue)
            if ($daysLeft <= 7) {
                // Check if we already notified today for this item
                $alreadyNotified = $admin->notifications()
                    ->where('data->meta->ticket_no', $pawnItem->ticket_no ?? '#'.$pawnItem->id)
                    ->where('data->meta->reminder_type', 'like', '%DUE%')
                    ->whereDate('created_at', today())
                    ->exists();

                if (! $alreadyNotified) {
                    $admin->notify(new PawnDueDateReminderNotification($pawnItem));
                    $notifiedCount++;
                }
            }
        }

        $this->info("Sent {$notifiedCount} due date reminder notifications.");
    }
}

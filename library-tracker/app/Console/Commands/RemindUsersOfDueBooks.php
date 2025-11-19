<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\NotifyUserOfDueLoan;
use Illuminate\Console\Command;

class RemindUsersOfDueBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remind-users-of-due-books';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to users with due books';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = 0;
        User::withCount('dueLoans')->whereHas('dueLoans')->chunk(200, function($users) use(&$count){
            foreach($users as $user){
                $user->notify(new NotifyUserOfDueLoan($user->due_loans_count));
                $count++;
            }
        });

        $this->info("{$count} users notified of overdue loans");
    }
}

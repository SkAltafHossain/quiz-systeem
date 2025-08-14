<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:fix-admin {email} {--grant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and optionally fix admin status for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $grant = $this->option('grant');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }
        
        $this->info("User found: {$user->name} <{$user->email}>");
        $this->line("Current admin status: " . ($user->is_admin ? 'Yes' : 'No'));
        $this->line("isAdmin() returns: " . ($user->isAdmin() ? 'true' : 'false'));
        
        if ($grant) {
            $user->is_admin = true;
            $user->save();
            $this->info("\nAdmin privileges have been granted to {$user->email}");
            $this->line("New admin status: " . ($user->is_admin ? 'Yes' : 'No'));
            $this->line("isAdmin() now returns: " . ($user->isAdmin() ? 'true' : 'false'));
        } else if (!$user->isAdmin()) {
            $this->warn("\nUser is not an admin. Use --grant flag to make this user an admin.");
            $this->line("Example: php artisan user:fix-admin {$email} --grant");
        }
        
        return 0;
    }
}

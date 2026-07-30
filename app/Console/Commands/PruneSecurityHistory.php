<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:prune-security-history')]
#[Description('Command description')]
class PruneSecurityHistory extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}

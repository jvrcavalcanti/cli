<?php

namespace App\Commands\Test;

use Accolon\Cli\Command;

class TestCommand extends Command
{
    protected string $signature = 'test';

    public function handle()
    {
        return 'Hihi' . PHP_EOL;
    }
}

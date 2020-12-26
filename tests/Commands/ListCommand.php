<?php

namespace App\Commands;

use Accolon\Cli\Command;
use Accolon\Cli\Event;

class ListCommand extends Command
{
    protected string $signature = 'list {dir}';
    protected string $description = 'List nothing';

    public function handle(Event $event)
    {
        throw new \Exception(':(');
        var_dump($this->hasFlag('--version'));
    }
}

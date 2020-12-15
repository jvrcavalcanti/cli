<?php

namespace App\Commands;

use Accolon\Cli\Command;
use Accolon\Cli\Event;

class ListCommand extends Command
{
    protected string $signature = 'list {dir} {file}';
    protected string $description = 'List nothing';

    public function handle(Event $event)
    {
        var_dump($this->argument('file'));
    }
}

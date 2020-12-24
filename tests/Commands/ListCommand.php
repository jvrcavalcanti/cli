<?php

namespace App\Commands;

use Accolon\Cli\Command;
use Accolon\Cli\CommandHandler;
use Accolon\Cli\Event;

#[Command]
class ListCommand extends CommandHandler
{
    protected string $signature = 'list {dir}';
    protected string $description = 'List nothing';

    public function handle(Event $event)
    {
        var_dump($this->argument('dir'));
    }
}

<?php

use Accolon\Cli\Console;
use Accolon\Cli\Command;
use Accolon\Cli\Event;

require_once './vendor/autoload.php';

function dd($var)
{
    var_dump($var);
    exit;
}

class ListCommand extends Command
{
    protected string $signature = 'list {dir} {file}';
    protected string $description = 'List nothing';

    public function handle(Event $event)
    {
        var_dump($this->argument('file'));
    }
}

Console::setContainer();

Console::addCommands([
    ListCommand::class
]);

Console::run(true, $argv);

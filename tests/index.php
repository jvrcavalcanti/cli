<?php

use Accolon\Cli\App;
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

App::setContainer();

App::addCommands([
    ListCommand::class
]);

App::run(true, $argv);

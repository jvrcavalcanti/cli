<?php

use Accolon\Cli\Console;

require_once './vendor/autoload.php';
require_once './tests/Commands/ListCommand.php';
require_once './tests/Commands/Test/TestCommand.php';

function dd($var)
{
    var_dump($var);
    exit;
}

Console::init();
Console::loadCommands();

// Console::addDirectory(__DIR__ . '/Commands', 'App\Commands');
echo Console::run(true, $argv);

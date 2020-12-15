<?php

use Accolon\Cli\Console;

require_once './vendor/autoload.php';

function dd($var)
{
    var_dump($var);
    exit;
}

Console::setContainer();

Console::addDirectory(__DIR__ . '/Commands', 'App\Commands');

echo Console::run(true, $argv);

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
        if ($this->hasFlag('-r')) {
            echo 'Recursive list' . PHP_EOL;
        }

        $tmp = scandir($this->argument('dir'));
        $files = array_splice($tmp, 2);

        foreach ($files as $file) {
            echo "File -> {$file}" . PHP_EOL;
        }
    }
}

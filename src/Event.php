<?php

namespace Accolon\Cli;

class Event
{
    private string $command;
    private array $args;

    public function __construct(array $args)
    {
        $this->command = $args[0];
        $this->args = count($args) > 1 ? array_splice($args, 1) : [];
    }

    public function say(string $message)
    {
        echo $message . "\n";
    }

    public function getcommand()
    {
        return $this->command;
    }

    public function getAllArgs()
    {
        return [$this->command, ...$this->args];
    }

    public function getArg(int $index)
    {
        return $this->args[$index];
    }

    public function getOptinal1()
    {
        return array_filter($this->args, fn(string $arg) => substr_count($arg, '-') < 2);
    }

    public function getOptinal2()
    {
        return array_filter($this->args, fn(string $arg) => substr_count($arg, '-') >= 2);
    }
}

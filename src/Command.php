<?php

namespace Accolon\Cli;

use Accolon\Cli\Exceptions\ValidatorException;

abstract class Command
{
    private array $keys = [];
    private array $args = [];
    private string $subject = '';
    private \ReflectionClass $reflectionClass;

    public function __construct()
    {
        $this->reflectionClass = new \ReflectionClass(static::class);

        if (!isset($this->signature)) {
            throw new \Exception('No signature');
        }

        $this->reflectionClass->getMethod('handle');

        if (!isset($this->description)) {
            $this->description = '';
        }

        $signature = $this->signature;

        preg_match_all("~\{\s* ([a-zA-Z_][a-zA-Z0-9_-]*) \}~x", $signature, $keys, PREG_SET_ORDER);

        $this->keys = array_map(fn($key) => $key[1], $keys);
    }

    public function getSignature()
    {
        return preg_replace('~{([^}]*)}~', "(.+)", $this->signature);
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getReflectionClass()
    {
        return $this->reflectionClass;
    }

    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    public function make(array $keys, array $args)
    {
        $this->args = array_map(fn($key) => $key[0], $keys);

        return $this->handle(...$args);
    }

    public function argument(string $name): ?string
    {
        if (!in_array($name, $this->keys)) {
            return null;
        }

        return $this->args[array_keys($this->keys, $name)[0]];
    }

    public function flag(string $name): ?string
    {
        return preg_match("#({$name}) (.+)#", $this->subject, $matches) ? $matches[2] : null;
    }

    public function hasFlag(string $name): bool
    {
        return preg_match("#{$name}#", $this->subject, $matches);
    }

    protected function validator(array $flags)
    {
        foreach ($flags as $flag) {
            if (!$this->hasFlag($flag)) {
                throw new ValidatorException("Flag [{$flag}] isn't");
            }
        }
    }
}

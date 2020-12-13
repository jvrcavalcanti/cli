<?php

namespace Accolon\Cli;

use Accolon\Container\Container;

class App
{
    /**
     * @var Command[] $commands
     */
    private static array $commands = [];
    private static ?Container $container = null;

    public static function setContainer(?Container $container = null)
    {
        static::$container = $container ?? new Container;
    }

    public static function resolveHandleCommand(Command $command, array $keys)
    {
        $reflectionClass = $command->getReflectionClass();

        $reflectionMethod = $reflectionClass->getMethod('handle');

        $params = $reflectionMethod->getParameters();

        $newParams = array_reduce($params, function (array $carry, \ReflectionParameter $param) {
            $type = (string) $param->getType();
            $carry[] = static::$container->make($type);
            return $carry;
        }, []);

        $command->make($keys, $newParams);
    }

    public static function addCommand(string $command)
    {
        static::$commands[] = static::$container->make($command);
    }

    public static function addCommands(array $commands)
    {
        foreach ($commands as $command) {
            static::addCommand($command);
        }
    }

    public static function printTime($start)
    {
        echo 'Runtime: ' . substr((microtime(true) - $start), 0, 6) . 'ms' . PHP_EOL;
    }

    public static function run(bool $time, array $args)
    {
        $args = array_splice($args, 1);
        $event = new Event($args);
        $subject = implode(' ', $args);

        static::$container->singletons(Event::class, $event);

        $start = microtime(true);
        
        foreach (static::$commands as $command) {
            if (preg_match_all("#{$command->getSignature()}#", $subject, $keys)) {
                static::resolveHandleCommand($command, array_splice($keys, 1));
                
                if ($time) {
                    static::printTime($start);
                }

                exit;
            }
        }
        
        echo 'Command not found' . PHP_EOL;
    }
}

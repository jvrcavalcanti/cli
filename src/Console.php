<?php

namespace Accolon\Cli;

use Accolon\Container\Container;

class Console
{
    /**
     * @var CommandHandler[] $commands
     */
    private static array $commands = [];
    private static Container $container;

    public static function setContainer(?Container $container = null)
    {
        static::$container = $container ?? new Container;
    }

    public static function resolveHandleCommand(CommandHandler $command, array $keys)
    {
        $reflectionClass = $command->getReflectionClass();

        $reflectionMethod = $reflectionClass->getMethod('handle');

        $params = $reflectionMethod->getParameters();

        $newParams = array_reduce($params, function (array $carry, \ReflectionParameter $param) {
            $type = (string) $param->getType();
            $carry[] = static::$container->make($type);
            return $carry;
        }, []);

        return $command->make($keys, $newParams);
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
        echo 'Runtime: ' . substr((microtime(true) - $start), 0, 10) . 'ms' . PHP_EOL;
    }

    public static function getDeclaredClasses()
    {
        $declaredClasses = get_declared_classes();
        $classes = array_splice($declaredClasses, 130);

        foreach ($classes as $class) {
            yield $class;
        }
    }

    public static function loadCommands()
    {
        $classes = static::getDeclaredClasses();

        foreach ($classes as $class) {
            $reflectionClass = new \ReflectionClass($class);
            $isCommand = (bool) count($reflectionClass->getAttributes(Command::class));

            if ($isCommand) {
                static::addCommand($class);
            }
        }
    }

    public static function run(bool $time, array $args)
    {
        $args = array_splice($args, 1);

        if (!count($args)) {
            throw new \RuntimeException('You must pass a command');
        }
        
        $event = new Event($args);
        $subject = implode(' ', $args);

        static::$container->singletons(Event::class, $event);

        $start = microtime(true);
        
        foreach (static::$commands as $command) {
            if (preg_match_all("#{$command->getSignature()}#", $subject, $keys)) {
                $result = static::resolveHandleCommand($command, array_splice($keys, 1));
                
                if ($time) {
                    static::printTime($start);
                }

                return $result;
            }
        }
        
        echo 'Command not found' . PHP_EOL;
    }
}

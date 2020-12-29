<?php

namespace Accolon\Cli;

use Accolon\Container\Container;

class Console
{
    /**
     * @var Command[] $commands
     */
    private static array $commands = [];
    private static Container $container;

    public static function setContainer(?Container $container = null)
    {
        static::$container = $container ?? new Container;
    }

    public static function init()
    {
        static::setContainer();
        static::loadCommands();
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
            if (is_subclass_of($class, Command::class)) {
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

        $cleanSubject = rtrim(preg_replace("#((-){1,}[a-zA-Z]{1,})#", "", $subject));
        
        foreach (static::$commands as $command) {
            if (preg_match_all("#{$command->getSignature()}#", $cleanSubject, $keys)) {
                $command->setSubject($subject);
                
                try {
                    $result = static::resolveHandleCommand($command, array_splice($keys, 1));

                    if ($time) {
                        static::printTime($start);
                    }

                    return $result;
                } catch (\Exception $e) {
                    $data = $e->getTrace()[0];
                    echo 'Error Exception' . PHP_EOL;
                    echo 'Message: ' . $e->getMessage() . PHP_EOL;
                    echo 'File: ' . $data['file'] . PHP_EOL;
                    echo 'Line: ' . $data['line'] . PHP_EOL;
                    return null;
                }
            }
        }
        
        echo 'Command not found' . PHP_EOL;
        return null;
    }
}

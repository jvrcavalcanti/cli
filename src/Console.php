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

    public static function addDirectory(string $path, string $namespace)
    {
        $files = scandir($path);
        $files = array_splice($files, 2);
        $files = array_filter($files, fn($file) => str_contains($file, '.php') || is_dir("{$path}/{$file}"));

        foreach ($files as $file) {
            if (is_dir("{$path}/{$file}")) {
                static::addDirectory("{$path}/{$file}", "{$namespace}\\{$file}");
                continue;
            }
            require_once "{$path}/{$file}";

            $shortName = explode('.', $file)[0];
            $fullName = "{$namespace}\\{$shortName}";
            static::addCommand($fullName);
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

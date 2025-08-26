<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Util;

final class AssocSerializer
{
    public static function from($anything): array
    {
        switch (true) {
            case $anything instanceof \Throwable:
                return self::throwable($anything);
            case \is_array($anything):
                return \array_map(
                    static function ($item) {
                        return self::from($item);
                    },
                    $anything
                );
            case $anything instanceof \JsonSerializable:
            case \is_object($anything):
                return self::basic($anything);
            default:
                return ['value' => $anything];
        }
    }

    private static function throwable(\Throwable $throwable): array
    {
        return [
            'class' => \get_class($throwable),
            'message' => $throwable->getMessage(),
            'code' => $throwable->getCode(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => self::trace($throwable),
        ];
    }

    private static function trace(\Throwable $throwable): string
    {
        $trace = $throwable->getTrace();
        $traceLenght = \count($trace);
        $pagLenght = \strlen((string)$traceLenght);

        $result = [];

        foreach ($trace as $key => $step) {
            $result[] = \sprintf(
                "#%s %s(%s)%s%s()",
                \str_pad((string)$key, $pagLenght, '0', \STR_PAD_LEFT),
                $step['file'] ?? '',
                $step['line'] ?? '',
                $step['type'] ?? '',
                $step['function'] ?? '',
            );
        }

        return \implode(\PHP_EOL, $result);
    }

    private static function basic($anything): array
    {
        return \json_decode(\json_encode($anything), true);
    }
}

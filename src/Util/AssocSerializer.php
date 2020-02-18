<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Util;

final class AssocSerializer
{
    public static function from($anything): array
    {
        switch (true) {
            case $anything instanceof \Throwable;
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
        $trace = \json_encode(
            $throwable->getTrace()
        );

        $trace = \is_string($trace)
            ? \json_decode($trace, true)
            : \print_r(
                $throwable->getTrace(),
                true
            )
        ;

        return [
            'class' => \get_class($throwable),
            'message' => $throwable->getMessage(),
            'code' => $throwable->getCode(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $trace,
        ];
    }

    private static function basic($anything): array
    {
        return \json_decode(
            \json_encode($anything),
            true
        );
    }
}

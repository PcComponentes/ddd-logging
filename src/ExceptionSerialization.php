<?php
declare(strict_types=1);
namespace Pccomponentes\DddLogging;

interface ExceptionSerialization
{
    public function message(string $parentOperationId, $message, \Throwable $exception): string;
    public function context(string $parentOperationId, $message, \Throwable $exception): array;
}

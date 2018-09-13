<?php
declare(strict_types=1);
namespace Pccomponentes\DddLogging;

interface SuccessSerialization
{
    public function message(string $parentOperationId, $message): string;
    public function context(string $parentOperationId, $message): array;
}

<?php
declare(strict_types=1);
namespace Pccomponentes\DddLogging;

interface Tracker
{
    public function parentOperationId(): string;
}

<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Action;

use PcComponentes\DddLogging\Action;

final class ProcessAction implements Action
{
    public function success(): string
    {
        return 'A message has been processed';
    }

    public function error(): string
    {
        return 'An exception occurred while processing the message';
    }
}

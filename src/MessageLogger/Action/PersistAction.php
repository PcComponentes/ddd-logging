<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Action;

use PcComponentes\DddLogging\Action;

final class PersistAction implements Action
{
    public function success(): string
    {
        return 'A message has been persisted';
    }

    public function error(): string
    {
        return 'An exception occurred while persisting the message';
    }
}

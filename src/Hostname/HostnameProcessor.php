<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Hostname;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class HostnameProcessor implements ProcessorInterface
{
    private string $host;

    public function __construct()
    {
        $this->host = \gethostname();
    }

    public function __invoke(LogRecord $record)
    {
        $record['extra']['hostname'] = $this->host;

        return $record;
    }
}

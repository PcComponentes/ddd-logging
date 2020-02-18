<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\Hostname;

use Monolog\Processor\ProcessorInterface;

final class HostnameProcessor implements ProcessorInterface
{
    private $host;

    public function __construct()
    {
        $this->host = gethostname();
    }

    public function __invoke(array $record): array
    {
        $record['extra']['hostname'] = $this->host;

        return $record;
    }
}

<?php
declare(strict_types=1);

namespace PcComponentes\DddLogging\MessageLogger;

interface Action
{
    public function success(): string;
    public function error(): string;
}

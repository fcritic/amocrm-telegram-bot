<?php

declare(strict_types=1);

namespace Integration\Worker\Interface;

use Symfony\Component\Console\Output\OutputInterface;

interface QueueWorkerInterface
{
    public function execute(OutputInterface $output): void;
}

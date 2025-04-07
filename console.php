<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;

$container = require __DIR__ . '/config/container.php';
$application = new Application('Application console');

$config = $container->get('config');
$commands = $config['dependencies']['console']['commands'];

foreach ($config['dependencies']['console']['commands'] as $name => $class) {
    $application->add($container->get($class));
}

try {
    $application->run();
} catch (\Exception $ex) {
    $output = new ConsoleOutput();
    $output->writeln('<error>' . $ex . '</error>');
}

<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Command\TransformCommand;
use Symfony\Component\Console\Application;

$application = new Application('CSV to JSON Transformer', '1.0.0');
$application->add(new TransformCommand());
$application->setDefaultCommand('transform', true);
$application->run();

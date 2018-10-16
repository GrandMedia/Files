<?php declare(strict_types = 1);

use Tester\Environment;
use Tester\Helpers;

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer update --dev`';
	exit(1);
}

\define('TEMP_DIR', __DIR__ . '/tmp/' . (isset($_SERVER['argv']) ? \md5(\serialize($_SERVER['argv'])) : \getmypid()));
// create temporary directory
Helpers::purge(\TEMP_DIR);

// configure environment
Environment::setup();

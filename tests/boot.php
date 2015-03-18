<?php
/*
 * This file is part of the HooksMock package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$autoload_path = dirname(__DIR__).'/vendor/autoload.php';
if (! file_exists($autoload_path)) {
    die('Please install via composer before running tests.');
}
require_once $autoload_path;

$helpers_path = dirname($autoload_path).'/phpunit/phpunit/src/Framework/Assert/Functions.php';
if (! file_exists($helpers_path)) {
    die('Please install via composer with dev option before running tests.');
}
require_once $helpers_path;

require_once __DIR__.'/stubs.php';

unset($autoload_path, $helpers_path);

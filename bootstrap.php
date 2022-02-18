<?php

declare(strict_types=1);

use Medas\Placeholder\PlaceholderPackage;
use Medas\ServiceManager\ServiceManager;

chdir(__DIR__);

require_once 'vendor/autoload.php';

$sm = ServiceManager::get();
$sm->addPackage(PlaceholderPackage::instance());

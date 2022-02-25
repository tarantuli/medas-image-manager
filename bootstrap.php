<?php

declare(strict_types=1);

use Medas\ImageManager\ImageManagerPackage;
use Medas\ServiceManager\ServiceManager;

chdir(__DIR__);

require_once 'vendor/autoload.php';

$sm = ServiceManager::get();
$sm->addPackage(ImageManagerPackage::instance());

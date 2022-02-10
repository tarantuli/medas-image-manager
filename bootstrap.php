<?php

declare(strict_types=1);

use Medas\JsonLdToAngularInterfaces\JsonLdToAngularInterfacesPackage;
use Medas\Placeholder\PlaceholderPackage;
use Medas\ServiceManager\ServiceManager;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Contracts\Cache\CacheInterface;

chdir(__DIR__);

require_once 'vendor/autoload.php';

$sm = ServiceManager::get();
$sm->addPackage(PlaceholderPackage::instance());

<?php

declare(strict_types=1);

use Medas\Cache\NoopCache;
use Medas\ServiceManager\Interfaces\Cache;

require_once __DIR__ . '/bootstrap.php';

$cache = new NoopCache('image-manager-test');
$cache->clear();
sm()->bindService($cache, Cache::class);

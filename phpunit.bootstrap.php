<?php

declare(strict_types=1);

use Medas\Cache\FilesystemCache;
use Medas\ServiceManager\Interfaces\Cache;

require_once __DIR__ . '/bootstrap.php';

$cache = new FilesystemCache(__DIR__ . '/var/cache');
$cache->clear();
sm()->bindService($cache, Cache::class);

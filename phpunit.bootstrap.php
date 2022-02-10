<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$cache = new ApcuAdapter('medas-placeholder');
$cache->clear();
sm()->bindService($cache, CacheInterface::class);

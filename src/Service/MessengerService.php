<?php
declare(strict_types=1);

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Messenger\Command\StopWorkersCommand;
use Symfony\Component\Messenger\EventListener\StopWorkerOnRestartSignalListener;

class MessengerService
{
    public function __construct(
        private CacheItemPoolInterface $restartSignalCachePool,
    )
    {
    }

    /**
     * Taken from the Symfony Command
     * @see StopWorkersCommand
     */
    public function stopAllWorkers(): void
    {
        $cacheItem = $this->restartSignalCachePool->getItem(StopWorkerOnRestartSignalListener::RESTART_REQUESTED_TIMESTAMP_KEY);
        $cacheItem->set(microtime(true));
        $this->restartSignalCachePool->save($cacheItem);
    }
}

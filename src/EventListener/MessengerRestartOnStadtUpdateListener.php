<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Stadt;
use App\Service\MessengerService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Stadt::class)]
class MessengerRestartOnStadtUpdateListener
{
    public function __construct(
        private MessengerService $messengerService,
    )
    {
    }

    public function postUpdate(Stadt $stadt, PostUpdateEventArgs $event): void
    {
        $this->messengerService->stopAllWorkers();
    }
}

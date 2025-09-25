<?php

namespace App\EventListener;

use App\Entity\IgAccount;
use App\Service\EncryptionService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preFlush, method: 'onPreFlush', entity: IgAccount::class)]
#[AsEntityListener(event: Events::postLoad, method: 'onPostLoad', entity: IgAccount::class)]
readonly class IgAccountListener
{
    public function __construct(
        private EncryptionService $encryptionService,
    ) {}

    public function onPreFlush(IgAccount $igAccount, PreFlushEventArgs $eventArgs)
    {
        if ($igAccount->hasUsernameChanged()) {
            $igAccount->setUsername($this->encryptionService->encrypt($igAccount->getUsername()), true);
        }

        if ($igAccount->hasLinkedAccountChanged()) {
            $igAccount->setLinkedAccount($this->encryptionService->encrypt($igAccount->getLinkedAccount()), true);
        }

        if ($igAccount->hasPasswordChanged()) {
            $igAccount->setPassword($this->encryptionService->encrypt($igAccount->getPassword()));
        }
    }

    public function onPostLoad(IgAccount $igAccount, PostLoadEventArgs $eventArgs)
    {
        $igAccount->setLinkedAccount($this->encryptionService->decrypt($igAccount->getLinkedAccount()));
        $igAccount->setUsername($this->encryptionService->decrypt($igAccount->getUsername()));
    }

}
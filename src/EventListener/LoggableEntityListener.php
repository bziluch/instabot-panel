<?php

namespace App\EventListener;

use App\Entity\Log;
use App\Entity\LoggableEntity;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: LoggableEntity::class)]
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: LoggableEntity::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: LoggableEntity::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: LoggableEntity::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: LoggableEntity::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: LoggableEntity::class)]
class LoggableEntityListener
{
    private array $pendingLogs = [];

    public function __construct(
        private readonly Security $security
    ) {}

    public function prePersist(LoggableEntity $entity, PrePersistEventArgs $args): void
    {
        $meta = $args->getObjectManager()->getClassMetadata(get_class($entity));
        $changes = [];
        foreach ($meta->getFieldNames() as $field) {
            if ($field != 'password') {
                $changes[$field] = ['old' => null, 'new' => $meta->getFieldValue($entity, $field)];
            } else {
                $changes[$field] = ['old' => null, 'new' => "*****"];
            }
        }

        $this->pendingLogs[spl_object_hash($entity)] = [
            'entity' => $entity,
            'action' => Log::ACTION_INSERT,
            'changes' => $changes,
            'user' => $this->getCurrentUser(),
        ];
    }

    public function postPersist(LoggableEntity $entity, PostPersistEventArgs $args): void
    {
        $this->flushLog($entity, $args->getObjectManager());
    }

    public function preUpdate(LoggableEntity $entity, PreUpdateEventArgs $args): void
    {
        $changes = [];
        foreach ($args->getEntityChangeSet() as $field => [$old, $new]) {
            if ($field != 'password') {
                $changes[$field] = ['old' => $old, 'new' => $new];
            } else {
                $changes[$field] = ['old' => "*****", 'new' => "*****"];
            }
        }

        if ($changes) {
            $meta = $args->getObjectManager()->getClassMetadata(get_class($entity));
            $idField = $meta->getSingleIdentifierFieldName();
            $entityId = $meta->getFieldValue($entity, $idField);

            $this->pendingLogs[spl_object_hash($entity)] = [
                'entityClass' => $meta->getName(),
                'entityId' => $entityId,
                'action' => Log::ACTION_UPDATE,
                'changes' => $changes,
                'user' => $this->getCurrentUser(),
            ];
        }
    }

    public function postUpdate(LoggableEntity $entity, PostUpdateEventArgs $args): void
    {
        $this->flushLog($entity, $args->getObjectManager());
    }

    public function preRemove(LoggableEntity $entity, PreRemoveEventArgs $args): void
    {
        $meta = $args->getObjectManager()->getClassMetadata(get_class($entity));
        $idField = $meta->getSingleIdentifierFieldName();
        $entityId = $meta->getFieldValue($entity, $idField);

        $changes = [];
        foreach ($meta->getFieldNames() as $field) {
            if ($field != 'password') {
                $changes[$field] = ['old' => $meta->getFieldValue($entity, $field), 'new' => null];
            } else {
                $changes[$field] = ['old' => "*****", 'new' => null];
            }
        }

        $this->pendingLogs[spl_object_hash($entity)] = [
            'entityClass' => $meta->getName(),
            'entityId' => $entityId,
            'action' => Log::ACTION_DELETE,
            'changes' => $changes,
            'user' => $this->getCurrentUser(),
        ];
    }

    public function postRemove(LoggableEntity $entity, PostRemoveEventArgs $args): void
    {
        $this->flushLog($entity, $args->getObjectManager());
    }

    private function flushLog(object $entity, $em): void
    {
        $oid = spl_object_hash($entity);
        if (!isset($this->pendingLogs[$oid])) {
            return;
        }

        $pending = $this->pendingLogs[$oid];
        unset($this->pendingLogs[$oid]);

        if (isset($pending['entity'])) {
            // insert – dopiero teraz mamy ID
            $meta = $em->getClassMetadata(get_class($pending['entity']));
            $idField = $meta->getSingleIdentifierFieldName();
            $entityId = $meta->getFieldValue($pending['entity'], $idField);

            $log = new Log(
                $meta->getName(),
                $entityId,
                $pending['action'],
                $pending['changes'],
                $pending['user']
            );
        } else {
            // update / delete
            $log = new Log(
                $pending['entityClass'],
                $pending['entityId'],
                $pending['action'],
                $pending['changes'],
                $pending['user']
            );
        }

        $em->persist($log);
        $em->flush();
    }

    private function getCurrentUser(): ?User
    {
        $user = $this->security->getUser();
        return $user instanceof User ? $user : null;
    }
}

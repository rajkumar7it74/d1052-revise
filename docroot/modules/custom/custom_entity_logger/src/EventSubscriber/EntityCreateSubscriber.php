<?php

namespace Drupal\custom_entity_logger\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Entity\Event\EntityInsertEvent;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\entity_events\Event\EntityEvent;
use Drupal\entity_events\EventSubscriber\EntityEventInsertSubscriber;

/**
 * Logs when entities are inserted.
 */
class EntityInsertSubscriber implements EventSubscriberInterface {

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs the subscriber.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory) {
    $this->logger = $logger_factory->get('entity_logger');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      //EntityEvent::INSERT => 'onEntityInsert',
    ];
  }

  /**
   * Logs when an entity is inserted.
   */
  public function onEntityInsert(EntityEvent $event): void {
    $entity = $event->getEntity();
    $type = $entity->getEntityTypeId();
    $label = $entity->label();

    $this->logger->notice("A new {$type} entity was inserted: {$label}");
  }

}

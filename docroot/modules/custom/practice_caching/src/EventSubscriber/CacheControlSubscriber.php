<?php

namespace Drupal\practice_caching\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CacheControlSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => 'onRespond',
    ];
  }

  public function onRespond(ResponseEvent $event) {
    $response = $event->getResponse();
    $response->headers->set('Cache-Control', 'public, max-age=0');
    \Drupal::service('page_cache_kill_switch')->trigger();
  }
}

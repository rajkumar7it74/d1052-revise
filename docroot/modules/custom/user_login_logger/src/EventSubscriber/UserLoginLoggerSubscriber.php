<?php

namespace Drupal\user_login_logger\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\User\UserLoginEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;

class UserLoginLoggerSubscriber implements EventSubscriberInterface {

  protected $logger;

  public function __construct(LoggerInterface $logger) {
    \Drupal::logger('default')->info('UserLoginLoggerSubscriber constructed');
    $this->logger = $logger;
  }

  public static function getSubscribedEvents(): array {
    return [
      UserLoginEvent::class => 'onUserLogin',
    ];
  }
  
  public function onUserLogin(UserLoginEvent $event): void {
    $account = $event->getAccount();
    $this->logger->info('User @name has logged in.', ['@name' => $account->getDisplayName()]);
    \Drupal::logger('default')->info('User @name has logged in.', ['@name' => $account->getDisplayName()]);
  }

}

<?php

namespace Drupal\user_login_logger\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Psr\Log\LoggerInterface;

class UserLoginBeforeSubscriber implements EventSubscriberInterface {

  protected $logger;

  public function __construct(LoggerInterface $logger) {
    $this->logger = $logger;
  }

  public static function getSubscribedEvents() {
    return [
      CheckPassportEvent::class => 'onCheckPassport',
    ];
  }

  public function onCheckPassport(CheckPassportEvent $event) {
    $passport = $event->getPassport();
    $userBadge = $passport->getBadge('user');
    if ($userBadge && $userBadge->getUser()) {
      $username = $userBadge->getUser()->getAccountName();
      $this->logger->info('User login attempt: ' . $username);
    }
  }
}

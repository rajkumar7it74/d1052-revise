<?php

namespace Drupal\token_based_remember_me\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Drupal\user\Entity\User;
use Drupal\Core\Database\Database;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RememberMeSubscriber implements EventSubscriberInterface {
    /**
     * 3. Validate Token on Page Load
     * Create src/EventSubscriber/RememberMeSubscriber.php:
     */

  protected $currentUser;

  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  public function onKernelRequest(RequestEvent $event) {
    if ($this->currentUser->isAuthenticated()) {
      return;
    }

    $request = $event->getRequest();
    $token = $request->cookies->get('token_based_remember_me');

    if ($token) {
      $connection = Database::getConnection();
      $record = $connection->select('remember_me_tokens', 'r')
        ->fields('r', ['uid', 'expires'])
        ->condition('token', $token)
        ->execute()
        ->fetchAssoc();

      if ($record && $record['expires'] > time()) {
        $user = User::load($record['uid']);
        if ($user) {
          user_login_finalize($user);
          $response = new RedirectResponse('/');
          $event->setResponse($response);
        }
      }
    }
  }

  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onKernelRequest', 100],
    ];
  }
}

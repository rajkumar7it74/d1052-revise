<?php

namespace Drupal\listen_event\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Logs a message when a request is made.
 */
class CustomEventSubscriber implements EventSubscriberInterface {

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs the subscriber.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory) {
    $this->logger = $logger_factory->get('custom_event');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['onRequest', 100],
    ];
  }

  /**
   * Logs when a request is made.
   */
  public function onRequest(RequestEvent $event): void {
    $this->logger->notice('CustomEventSubscriber triggered on request.');
    \Drupal::logger('custom_event')->notice('logFile method triggered.');
    $routeName    = \Drupal::routeMatch()->getRouteName();
    $current_path = \Drupal::request()->getRequestUri();
    dump($current_path);die;
    if($routeName == 'entity.node.canonical') {
      $filename = 'public://customevent.txt';
      $file   = fopen($filename,'a');
      fwrite($file,$current_path);
      fclose($file);
    }
  }
}
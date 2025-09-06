<?php

namespace Drupal\user_data_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;

class UserDataController extends ControllerBase {

  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  public function listUsers() {
    $query = $this->database->select('users_field_data', 'u')
      ->fields('u', ['uid', 'name', 'mail'])
      ->condition('status', 1)
      ->range(0, 10);
    $results = $query->execute()->fetchAll();

    $items = [];
    foreach ($results as $user) {
      $items[] = [
        '#markup' => $this->t('User ID: @uid, Name: @name, Email: @mail', [
          '@uid' => $user->uid,
          '@name' => $user->name,
          '@mail' => $user->mail,
        ]),
      ];
    }

    return [
      '#type' => 'item_list',
      '#items' => $items,
      '#cache' => ['max-age' => 0], // Disable render caching
    ];
  }
}

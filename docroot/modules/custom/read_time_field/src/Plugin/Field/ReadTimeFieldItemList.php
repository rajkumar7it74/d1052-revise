<?php

namespace Drupal\read_time_field\Plugin\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\ComputedItemListInterface;
//use Drupal\Core\Field\ComputedItemListTrait;

class ReadTimeFieldItemList extends FieldItemList {
  //use ComputedItemListTrait;

  protected function computeValue() {
    $entity = $this->getEntity();

    // Safety checks
    if (!$entity || !$entity->id() || $entity->getEntityTypeId() !== 'node') {
      return;
    }

    try {
      $config = \Drupal::config('read_time_field.settings');
      $wpm = $config->get('words_per_minute') ?? 200;
      $fields = $config->get('text_fields') ?? ['body'];

      $text = '';
      foreach ($fields as $field_name) {
        if ($entity->hasField($field_name) && !$entity->get($field_name)->isEmpty()) {
          $text .= ' ' . $entity->get($field_name)->value;
        }
      }

      $word_count = str_word_count(strip_tags($text));
      $minutes = ceil($word_count / $wpm);

      // Set computed value
      $this->list[0] = $this->createItem(0, $minutes);

    } catch (\Throwable $e) {
      // Fallback to zero if anything fails
      $this->list[0] = $this->createItem(0, 0);
    }
  }
}

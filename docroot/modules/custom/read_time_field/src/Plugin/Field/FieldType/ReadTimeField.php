<?php

namespace Drupal\read_time_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;
//use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * @FieldType(
 *   id = "read_time",
 *   label = @Translation("Read Time"),
 *   description = @Translation("Estimated time to read blog content."),
 *   default_formatter = "read_time_formatter",
 *   default_widget = "number",
 *   default_formatter = "number_integer",
 *   list_class = "\Drupal\read_time_field\Plugin\Field\ReadTimeFieldItemList"
 * )
 */
class ReadTimeField extends FieldItemBase {
  use ComputedItemListTrait;

  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Read time in minutes'));
    return $properties;
  }

  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'int',
          'unsigned' => TRUE,
        ],
      ],
    ];
  }

  // public function computeValue() {
  //   try {
  //     $entity = $this->getEntity();
  //     $config = \Drupal::config('read_time_field.settings');
  //     $wpm = $config->get('words_per_minute') ?? 200;
  //     $fields = $config->get('text_fields') ?? ['body'];

  //     $cache_key = 'read_time:' . $entity->id();
  //     $cached = \Drupal::cache()->get($cache_key);
  //     if ($cached) {
  //       $this->setValue($cached->data);
  //       return;
  //     }
  
  //     $text = '';
  //     foreach ($fields as $field_name) {
  //       if ($entity->hasField($field_name)) {
  //         $text .= ' ' . $entity->get($field_name)->value;
  //       }
  //     }
  
  //     $word_count = str_word_count(strip_tags($text));
  //     $minutes = ceil($word_count / $wpm);
  //     \Drupal::cache()->set($cache_key, $minutes, strtotime('+1 day'));
  //     $this->setValue($minutes);
  //   } catch (\Throwable $e) {
  //     $this->setValue(0); // fallback to prevent crash
  //   }
  // }

  public function computeValue() {
    try {
      $entity = $this->getEntity();
  
      // Only proceed if the entity is a node and has an ID
      if (!$entity || !$entity->id() || $entity->getEntityTypeId() !== 'node') {
        $this->setValue(0);
        return;
      }
  
      $config = \Drupal::config('read_time_field.settings');
      $wpm = $config->get('words_per_minute') ?? 200;
      $fields = $config->get('text_fields') ?? ['body'];
  
      // Build cache key
      $cache_key = 'read_time:' . $entity->id();
      $cached = \Drupal::cache()->get($cache_key);
      if ($cached) {
        $this->setValue($cached->data);
        return;
      }
  
      // Collect text from configured fields
      $text = '';
      foreach ($fields as $field_name) {
        if ($entity->hasField($field_name) && !$entity->get($field_name)->isEmpty()) {
          $text .= ' ' . $entity->get($field_name)->value;
        }
      }
  
      $word_count = str_word_count(strip_tags($text));
      $minutes = ceil($word_count / $wpm);
  
      // Cache for 24 hours
      \Drupal::cache()->set($cache_key, $minutes, strtotime('+1 day'));
      $this->setValue($minutes);
  
    } catch (\Throwable $e) {
      // Fallback to prevent fatal error
      $this->setValue(0);
    }
  }
}

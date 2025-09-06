<?php

namespace Drupal\read_time_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * @FieldFormatter(
 *   id = "read_time_formatter",
 *   label = @Translation("Read Time Badge"),
 *   field_types = {
 *     "read_time"
 *   }
 * )
 */
class ReadTimeFormatter extends FormatterBase {

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#markup' => '<span class="read-time-badge">' . $this->t('@count min read', ['@count' => $item->value]) . '</span>',
        '#attached' => [
          'library' => ['read_time_field/read_time_styles'],
        ],
      ];
    }
    return $elements;
  }
}

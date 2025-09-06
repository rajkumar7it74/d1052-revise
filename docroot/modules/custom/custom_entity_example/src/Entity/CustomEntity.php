<?php

namespace Drupal\custom_entity_example\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the CustomEntity entity.
 *
 * @ContentEntityType(
 *   id = "custom_entity",
 *   label = @Translation("Custom Entity"),
 *   base_table = "custom_entity_example_table",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name"
 *   },
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler"
 *   },
 *   links = {
 *     "canonical" = "/custom_entity/{custom_entity}",
 *     "add-form" = "/custom_entity/add",
 *     "edit-form" = "/custom_entity/{custom_entity}/edit",
 *     "delete-form" = "/custom_entity/{custom_entity}/delete"
 *   }
 * )
 */
class CustomEntity extends ContentEntityBase {
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Name'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ]);
    return $fields;
  } 
}
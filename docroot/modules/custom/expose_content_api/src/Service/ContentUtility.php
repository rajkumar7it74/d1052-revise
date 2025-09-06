<?php

namespace Drupal\expose_content_api\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\node\Entity\Node;
//use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Utility service for handling content-related operations.
 */
class ContentUtility {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  protected $entityTypeManager;
  /**
   * @var \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   */
  protected $fileUrlGenerator;
  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  //protected $configFactory;

  /**
   * Constructor to inject dependencies.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    FileUrlGeneratorInterface $fileUrlGenerator,
    //ConfigFactoryInterface $configFactory
    ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fileUrlGenerator = $fileUrlGenerator;
    //$this->configFactory = $configFactory;
  }

  /**
   * Returns the absolute URL of an image field from a node.
   */
  public function getNodeImageUrl(Node $node, $field_name) {
    if ($node->hasField($field_name) && !$node->get($field_name)->isEmpty()) {
      $file = $node->get($field_name)->entity;
      return $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
    }
    return NULL;
  }

  /**
   * Loads all nodes regardless of type.
   */
  public function loadAllNodes() {
    $nids = $this->entityTypeManager->getStorage('node')->getQuery()->accessCheck(TRUE)->execute();
    return $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
  }

  /**
   * Loads nodes of a specific content type.
   */
  public function loadSpecificTypeNodes($type) {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    if ($type) {
      $query->condition('type', $type);
    }
    $nids = $query->accessCheck(TRUE)->execute();
    if (empty($nids)) {
      return [];
    }
    return $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
  }

  /**
   * Returns node fields based on content type.
   */
  public function getNodeFieldsBasedOnContentType($node) {
    $data = [];
    if ($node->bundle() === 'recipe') {
      $data = $this->getRecipeNodeFields($node);
    }
    if ($node->bundle() === 'page') {
      $data = $this->getBasicPageNodeFields($node);
    }
    if ($node->bundle() === 'article') {
      $data = $this->getArticleNodeFields($node);
    }
    return $data;
  }

  /**
   * Extracts fields specific to 'recipe' content type.
   */
  public function getRecipeNodeFields($node) {
    $data = [
      'id' => $node->id(),
      'title' => $node->label(),
      'recipe_description' => $node->hasField('field_recipe_description') ? $node->get('field_recipe_description')->value : '',
      'recipe_dish_type' => $node->hasField('field_recipe_dish_type') ? $node->get('field_recipe_dish_type')->value : '',
      'recipe_image' => $this->getNodeImageUrl($node, 'field_recipe_image'),
      'recipe_ingredients' => $node->hasField('field_recipe_ingredients') ? $node->get('field_recipe_ingredients')->value : '',
      'recipe_instructions' => $node->hasField('field_recipe_instructions') ? $node->get('field_recipe_instructions')->value : '',
      'recipe_total_time' => $node->hasField('field_recipe_total_time') ? $node->get('field_recipe_total_time')->value : '',
    ];
    return $data;
  }

  /**
   * Extracts fields specific to 'page' content type.
   */
  public function getBasicPageNodeFields($node) {
    $data = [
      'id' => $node->id(),
      'title' => $node->label(),
      'body' => $node->hasField('body') ? $node->get('body')->value : '',
    ];
    return $data;
  }

  /**
   * Extracts fields specific to 'article' content type.
   */
  public function getArticleNodeFields($node) {
    $data = [
      'id' => $node->id(),
      'title' => $node->label(),
      'body' => $node->hasField('body') ? $node->get('body')->value : '',
      'article_image' => $this->getNodeImageUrl($node, 'field_image'),
      'article_tags' => $node->hasField('field_tags') ? $this->getAllTags($node->get('field_tags')) : '',
    ];
    return $data;
  }

  /**
   * Converts taxonomy term references to a comma-separated string of labels.
   */
  public function getAllTags($tags) {
    $categories = [];
    foreach ($tags as $tag) {
      $term = $tag->entity;
      $categories[] = $term->label();
    }
    return implode(', ', $categories);
  }

  /**
   * Loads a specific node of a given type and ID.
   */
  public function loadSpecificTypeSpecificNode($type, $node_id) {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    if ($type) {
      $query->condition('type', $type);
    }
    if (isset($node_id)) {
      $query->condition('nid', $node_id);
    }
    $nid = $query->accessCheck(TRUE)->execute();
    if (empty($nid)) {
      return [];
    }
    return $this->entityTypeManager->getStorage('node')->load(reset($nid));
  }

//   public function getResponsiveImageConfig(ConfigFactoryInterface $config_factory) {
//     $config = $this->configFactory->get('your_module.responsive_image_settings');
  
//     return [
//       'responsive_styles' => $config->get('responsive_styles'),
//       'lazyloader' => $config->get('lazyloader'),
//       'fallback_style' => $config->get('fallback_style'),
//       'caption' => $config->get('caption'),
//     ];
//   }
}

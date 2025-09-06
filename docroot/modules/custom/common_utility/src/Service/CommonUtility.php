<?php

namespace Drupal\common_utility\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\node\Entity\Node;
//use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Utility\EmailValidator;
use Drupal\Core\Database\Connection;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Utility service for handling content-related operations.
 */
class CommonUtility {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  protected $entityTypeManager;
  /**
   * @var \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   */
  protected $fileUrlGenerator;

  /**
   * The Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  //protected $configFactory;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection $database
   */
  protected $database;
  
  /**
   * The email validator.
   *
   * @var \Drupal\Component\Utility\EmailValidator
   */
  protected $emailValidator;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;
 
  /**
   * Constructor to inject dependencies.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    FileUrlGeneratorInterface $fileUrlGenerator,
    //ConfigFactoryInterface $configFactory,
    Connection $database,
    EmailValidator $emailValidator,
    AccountProxyInterface $current_user) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fileUrlGenerator = $fileUrlGenerator;
    //$this->configFactory = $configFactory;
    $this->database = $database;
    $this->emailValidator = $emailValidator;
    $this->currentUser = $current_user;
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
  //$this->configFactory->get('your_module.responsive_image_settings');

  /**
   * Function to validate DOB if it is less than 18 years old.
   */
  public function validateDobField($dob) {
    $today = new \DateTime();
    $dob_value = new \DateTime($dob);
    $interval = $today->diff($dob_value);
    if ($interval->y < 18) {
      return FALSE;
    }
  }

  /**
   * Function to validate aadhaar number.
   */
  public function validateAadhar($adhaar) {
    if (!preg_match("/^[2-9]{1}[0-9]{3}-[0-9]{4}-[0-9]{4}$/", trim($adhaar))) {
      return false;
    }
  }

  /**
   * Check email address is valid or not.
   * If email is valid then it will return email address
   * otherwise it will return false.
   */
  public function checkValidEmail($email) {
    if ($this->emailValidator->isValid($email)) {
      // It will return email address if email is correct.
      //dump(filter_var($email, FILTER_VALIDATE_EMAIL));die('..checkValidEmail...');
      if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
      }
      return false;
    }
  }

  /**
   * Check if email id is already exists.
   */
  public function emailAlreadyExists($email) {
    $storage = $this->entityTypeManager->getStorage('user');
    $users = $storage->loadByProperties(['mail' => $email]);
    return !empty($users) ? TRUE : FALSE;

  }

  /**
   * Function to generate html5 picture tag from image uri.
   */
  public function processImageToPictureTag($image_uri) {
    $output = '';
    $imageStylesToProcessWithMediaTag = [
      'recipe_small' => 'min-width: 1024px',
      'recipe_medium' => 'min-width: 768px',
      'recipe_large' => 'max-width: 767px'
    ];
    $responsiveImageSettings = \Drupal::config('user_extend.responsive_image_settings');
    dump($responsiveImageSettings);
    $output .= '<picture>';
    foreach ($imageStylesToProcessWithMediaTag as $styleName => $mediaTag) {
      $styledImageUrl = $this->getStyledImageUrl($image_uri, $styleName);
      $imageSourceTag = $this->wrapStyledImageToSourceTag($styledImageUrl, $mediaTag);
      $output .= $imageSourceTag;
    }
    //$output .= $this->createImageUrlWithLazyLoader($image_uri, $image_Style, $lazyLoading, $title);
    $output .= $this->createImageUrlWithLazyLoader($image_uri, 'recipe_medium', TRUE, 'title');
    $output .= '</picture>';
    
  }

  /**
   * Function to get image styled url.
   */
  public function getStyledImageUrl($image_uri, $image_style) {
    return ImageStyle::load($image_style)->buildUrl($image_uri);
  }
  /**
   * Function to wrap image styled url in source tag of picture tag.
   */
  public function wrapStyledImageToSourceTag($image_url, $media) {
    $output = '"<source srcset=" '. $image_url .'" media="(' . $media . '")>';
    return $output;
  }
  /**
   * Function to generate img tag from image uri to picture tag.
   */
  public function createImageUrlWithLazyLoader($image_uri, $image_Style, $lazyLoading, $title) {
    if ($lazyLoading) {
      $lazy = 'lazy';
    }
    $output = '<img src="';
    $output .= $this->getStyledImageUrl($image_uri, $image_Style);
    $output .= 'alt="' . $title . '" loading="' . $lazy . '">';
    return $output;
  }

  /**
   * Function to get image alt text.
   */
  public function getImageAltText($fileEntity) {
    return $fileEntity->alt;
  }

  /**
   * Function to register user with email id.
   */
  public function registerUserWithEmail($email) {
    if ($this->emailAlreadyExists($email)) {
      return FALSE;
    }
    $user_storage = $this->entityTypeManager->getStorage('user');

    // Create a new user.
    $user = $user_storage->create([
      'name' => $email,// Required: username
      'mail' => $email,// Required: email
      'status' => 0,// 1 = active, 0 = blocked
      'roles' => ['authenticated'],// Optional: assign roles
    ]);

    // Save the user.
    $user->save();
    return $user;
  }

  /**
   * Function to create node.
   */
  public function createNode($type, $fields) {
    if ($type === 'recipe') {
      return $this->createRecipeContent($type, $fields);
    }
    if ($type === 'page') {
      return $this->createPageContent($type, $fields);
    }
    if ($type === 'article') {
      return $this->createArticleContent($type, $fields);
    }
  }

  /**
   * Function to create recipe content.
   */
  public function createRecipeContent($type, $fields) {
    $nodeObject = $this->entityTypeManager->getStorage('node')->create([
      'type' => $type,
      'title' => $fields['title'],
      'field_recipe_description' => $fields['recipe_description'],
      'field_recipe_dish_type' => $fields['recipe_dish_type'],
      'field_recipe_ingredients' => $fields['recipe_ingredients'],
      'field_recipe_instructions' => $fields['recipe_instructions'],
      'field_recipe_total_time' => $fields['recipe_total_time'],
      'status' => 1,
    ]);
    $node = $nodeObject->save();
    if ($node) {
      return $node;
    }
    return false;
  }

  /**
   * Function to create basic page content.
   */
  public function createPageContent($type, $fields) {
    $nodeObject = $this->entityTypeManager->getStorage('node')->create([
      'type' => $type,
      'title' => $fields['title'],
      'field_body' => $fields['body'],
      'status' => 1,
    ]);
    $node = $nodeObject->save();
    if ($node) {
      return $node;
    }
    return false;
  }

  /**
   * Function to create article content.
   */
  public function createArticleContent($type, $fields) {
    $nodeObject = $this->entityTypeManager->getStorage('node')->create([
      'type' => $type,
      'title' => $fields['title'],
      'field_body' => $fields['body'],
      'field_tags' => $fields['tags'],
      'status' => 1,
    ]);
    $node = $nodeObject->save();
    if ($node) {
      return $node;
    }
    return false;
  }
}

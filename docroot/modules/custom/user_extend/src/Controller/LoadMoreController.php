<?php

namespace Drupal\user_extend\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\views\Views;
use Drupal\image\Entity\ImageStyle;
use Drupal\common_utility\Service\CommonUtility;

class LoadMoreController extends ControllerBase {
  public function load(Request $request) {
    $page = $request->query->get('page', 1);
    //$view = views_get_view('articles');
    $view = Views::getView('custom_image_gallery');
    if (!$view) {
      return new Response('View not found', 404);
    }
    $view->setDisplay('page_1');
    $view->setArguments([$page]);
    $view->execute();


    $output = '';
    foreach ($view->result as $key => $row) {
      $output .= '<div class="article-row"><picture>';
      //dump($row);
      $responsiveImageSettings = \Drupal::config('user_extend.responsive_image_settings');
    //dump($responsiveImageSettings);
      if (isset($row->_entity)) {
        $entity = $row->_entity;
        $this->getImagesInSource($entity, 'field_recipe_image');
        // if ($entity->hasField('field_recipe_image') && !$entity->get('field_recipe_image')->isEmpty()) {
        //   $image_uri = $entity->get('field_recipe_image')->entity->getFileUri();
        //   if ($image_uri) {
        //     $images[$key]['small_image'] = ImageStyle::load('recipe_small')->buildUrl($image_uri);
        //     $images[$key]['medium_image'] = ImageStyle::load('recipe_medium')->buildUrl($image_uri);
        //     $images[$key]['large_image'] = ImageStyle::load('recipe_large')->buildUrl($image_uri);
        //     $images[$key]['title'] = $entity->label();
        //   }
        // }
        $this->getImagesInSource($entity, 'field_image');
        // if ($entity->hasField('field_image') && !$entity->get('field_image')->isEmpty()) {
        //   $image_uri = $entity->get('field_image')->entity->getFileUri();
        //   if ($image_uri) {
        //     $images[$key]['small_image'] = ImageStyle::load('recipe_small')->buildUrl($image_uri);
        //     $images[$key]['medium_image'] = ImageStyle::load('recipe_medium')->buildUrl($image_uri);
        //     $images[$key]['large_image'] = ImageStyle::load('recipe_large')->buildUrl($image_uri);
        //     $images[$key]['title'] = $entity->label();
        //   }
        // }
      }
      $output .= '<div class="article-row">' . $row->_entity->label() . '</div>';
    }

    return new Response($output);
  }
  public function getImagesInSource($entity, $image_field) {
    $common_utility = \Drupal::service('common_utility.common_utility');
    if ($entity->hasField($image_field) && !$entity->get($image_field)->isEmpty()) {
      $fileEntity = $entity->get($image_field)->entity;
      $image_uri = $entity->get($image_field)->entity->getFileUri();
    
      $imageAlt = $common_utility->getImageAltText($fileEntity);
      dump($imageAlt);

      if ($image_uri) {
        //$this->getImageSourceTag($image_uri);
        $images['small_image'] = ImageStyle::load('recipe_small')->buildUrl($image_uri);
        $images['medium_image'] = ImageStyle::load('recipe_medium')->buildUrl($image_uri);
        $images['large_image'] = ImageStyle::load('recipe_large')->buildUrl($image_uri);
        $images['title'] = $entity->label();
      }
    }
    if ($entity->hasField('field_image') && !$entity->get('field_image')->isEmpty()) {
      $image_uri = $entity->get('field_image')->entity->getFileUri();
      if ($image_uri) {
        $images['small_image'] = ImageStyle::load('recipe_small')->buildUrl($image_uri);
        $images['medium_image'] = ImageStyle::load('recipe_medium')->buildUrl($image_uri);
        $images['large_image'] = ImageStyle::load('recipe_large')->buildUrl($image_uri);
        $images['title'] = $entity->label();
      }
    }
  }
  // public function getImageSourceTag($image_uri) {
  //   $images = $this->getAllImagesOfStyles($image_uri);
  //   $output = '';
  //   $output .= "<source srcset=";
  //   // foreach($images as $key => $value) {
  //   //   $output .= $value[];
  //   // }
    
  //   $output .= ImageStyle::load('recipe_small')->buildUrl($image_uri);

  // }

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

  public function getAllImagesOfStyles($image_uri) {
    $images = [];
    $images['small_image'] = $this->getStyledImageUrl($image_uri, 'recipe_small');
    //ImageStyle::load('recipe_small')->buildUrl($image_uri);
    $images['medium_image'] = $this->getStyledImageUrl($image_uri, 'recipe_medium');
    //ImageStyle::load('recipe_medium')->buildUrl($image_uri);
    $images['large_image'] = $this->getStyledImageUrl($image_uri, 'recipe_large');
    //ImageStyle::load('recipe_large')->buildUrl($image_uri);
    //$images['title'] = $entity->label();
  }
  public function getStyledImageUrl($image_uri, $image_style) {
    return ImageStyle::load($image_style)->buildUrl($image_uri);
  }
  public function wrapStyledImageToSourceTag($image_url, $media) {
    $output = '"<source srcset=" '. $image_url .'" media="(' . $media . '")>';
    return $output;
  }
  public function createImageUrlWithLazyLoader($image_uri, $image_Style, $lazyLoading, $title) {
    if ($lazyLoading) {
      $lazy = 'lazy';
    }
    $output = '<img src="';
    $output .= $this->getStyledImageUrl($image_uri, $image_Style);
    $output .= 'alt="' . $title . '" loading="' . $lazy . '">';
    return $output;
  }
}

// {% for image in images %}
//     <div class="article-row">
//       <picture>
//         <source srcset="{{ image['large_image'] }}" media="(min-width: 1024px)">
//         <source srcset="{{ image['medium_image'] }}" media="(min-width: 768px)">
//         <source srcset="{{ image['small_image'] }}" media="(max-width: 767px)">
//         <img src="{{ image['medium_image'] }}" alt="{{ image['label'] }}" loading="lazy">
//       </picture>
//     </div>
//   {% endfor %}

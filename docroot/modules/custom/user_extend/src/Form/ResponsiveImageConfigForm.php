<?php

namespace Drupal\user_extend\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Entity\ImageStyle;

class ResponsiveImageConfigForm extends ConfigFormBase {

  public function getFormId() {
    return 'responsive_image_config_form';
  }

  protected function getEditableConfigNames() {
    return ['user_extend.responsive_image_settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $image_styles = ImageStyle::loadMultiple();
    $style_options = [];
    foreach ($image_styles as $id => $style) {
      $style_options[$id] = $style->label();
    }

    $config = $this->config('user_extend.responsive_image_settings');

    // 1. Multi-select dropdown (max 3)
    $form['responsive_styles'] = [
      '#type' => 'select',
      '#title' => $this->t('Responsive Image Styles'),
      '#options' => $style_options,
      '#default_value' => $config->get('responsive_styles') ?? [],
      '#multiple' => TRUE,
      '#description' => $this->t('Select up to 3 image styles for responsive images.'),
    ];

    // 2. Lazyloader checkbox
    $form['lazyloader'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Lazy Loading'),
      '#default_value' => $config->get('lazyloader') ?? FALSE,
    ];

    // 3. Single-select dropdown for <img> tag
    $form['fallback_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Fallback Image Style'),
      '#options' => $style_options,
      '#default_value' => $config->get('fallback_style') ?? '',
      '#description' => $this->t('Used for the <img> tag inside <picture>.'),
    ];

    // 4. Textarea limited to 3 lines
    $form['style_viewport_map'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Map Image Style to Viewport'),
      '#default_value' => $config->get('style_viewport_map') ?? '',
      '#description' => $this->t('Define mappings in the format: image_style_name: min-width (e.g., "large 1024px"). Maximum 3 lines allowed.'),
      '#rows' => 3,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $selected_styles = $form_state->getValue('responsive_styles');
    if (count($selected_styles) > 3) {
      $form_state->setErrorByName('responsive_styles', $this->t('You can select up to 3 image styles.'));
    }

    $mapping = trim($form_state->getValue('style_viewport_map'));
    if (substr_count($mapping, "\n") > 2) {
      $form_state->setErrorByName('style_viewport_map', $this->t('You may only define up to 3 mappings.'));
    }

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('user_extend.responsive_image_settings')
      ->set('responsive_styles', $form_state->getValue('responsive_styles'))
      ->set('lazyloader', $form_state->getValue('lazyloader'))
      ->set('fallback_style', $form_state->getValue('fallback_style'))
      ->set('style_viewport_map', $form_state->getValue('style_viewport_map'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}

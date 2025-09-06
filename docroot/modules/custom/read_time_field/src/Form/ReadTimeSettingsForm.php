<?php

namespace Drupal\read_time_field\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ReadTimeSettingsForm extends ConfigFormBase {
  protected function getEditableConfigNames() {
    return ['read_time_field.settings'];
  }

  public function getFormId() {
    return 'read_time_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('read_time_field.settings');

    $form['words_per_minute'] = [
      '#type' => 'number',
      '#title' => $this->t('Words per minute'),
      '#default_value' => $config->get('words_per_minute') ?? 200,
      '#min' => 50,
      '#max' => 1000,
      '#description' => $this->t('Average reading speed used to calculate read time.'),
    ];

    $form['text_fields'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text fields to include'),
      '#default_value' => implode(',', $config->get('text_fields') ?? ['body']),
      '#description' => $this->t('Comma-separated list of field machine names to include in read time calculation.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('read_time_field.settings')
      ->set('words_per_minute', $form_state->getValue('words_per_minute'))
      ->set('text_fields', array_map('trim', explode(',', $form_state->getValue('text_fields'))))
      ->save();
    parent::submitForm($form, $form_state);
  }
}

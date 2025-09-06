<?php

namespace Drupal\user_extend\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class UserExistenceCheckForm extends ConfigFormBase {

  public function getFormId() {
    return 'user_existence_check_form';
  }

  protected function getEditableConfigNames() {
    return ['user_extend.user_existence_settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('user_extend.user_existence_settings');

    $form['check_email'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Check if Email ID already exists'),
      '#default_value' => $config->get('check_email') ?? FALSE,
    ];

    $form['check_aadhar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Check if Aadhaar number already exists'),
      '#default_value' => $config->get('check_aadhar') ?? FALSE,
    ];

    $form['check_phone'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Check if Phone number already exists'),
      '#default_value' => $config->get('check_phone') ?? FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('user_extend.user_existence_settings')
      ->set('check_email', $form_state->getValue('check_email'))
      ->set('check_aadhar', $form_state->getValue('check_aadhar'))
      ->set('check_phone', $form_state->getValue('check_phone'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}

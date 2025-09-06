<?php

namespace Drupal\user_extend\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

class AjaxMessageForm extends FormBase {

  public function getFormId() {
    return 'ajax_message_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['message_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'message-div'],
    ];

    $form['message_container']['clickme'] = [
      '#type' => 'button',
      '#value' => $this->t('Click Me'),
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => 'message-div',
        'effect' => 'fade',
      ],
    ];

    return $form;
  }

  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#message-div', '<div class="message">Hello, Button is clicked.</div>'));
    return $response;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Not used in this example
  }
}

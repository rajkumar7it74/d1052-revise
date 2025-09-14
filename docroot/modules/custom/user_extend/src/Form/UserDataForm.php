<?php

namespace Drupal\user_extend\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\EmailValidator;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\common_utility\Service\CommonUtility;
use Drupal\encrypt\EncryptServiceInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class UserDataForm extends FormBase {
  
  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;
  
  /**
   * The email validator.
   *
   * @var \Drupal\Component\Utility\EmailValidator
   */
  protected $emailValidator;

  protected $commonUitlity;

  protected $encryptionService;
  
  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Constructs a new UserDataForm.
   */
  public function __construct(Connection $database,
    EmailValidator $emailValidator,
    CommonUtility $commonUitlity,
    EncryptServiceInterface $encryptionService,
    EntityTypeManagerInterface $entityTypeManager) {
    $this->database = $database;
    $this->emailValidator = $emailValidator;
    $this->commonUitlity = $commonUitlity;
    $this->encryptionService = $encryptionService;
    $this->entityTypeManager = $entityTypeManager;
  }
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('email.validator'),
      $container->get('common_utility.common_utility'),
      $container->get('encryption'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Returns the unique ID of the form.
   *
   * @return string
   *   The unique form ID.
   */
  public function getFormId() {
    return 'user_data_collection_form';
  }

  /**
   * Builds the user data form.
   *
   * This function defines the structure of the form, including its
   * fields, labels, and submit button.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('Please enter your name'),
      ],

    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('Please enter your email'),
      ],
      '#suffix' => '<div id="email_validation_error"></div>',
    ];
    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone'),
      '#required' => TRUE,
      '#maxlength' => 12,
      '#attributes' => [
        'placeholder' => $this->t('Please enter your phone number'),
      ],
      '#suffix' => '<div id="phone_validation_error"></div>',
    ];
    $form['dob'] = [
      '#type' => 'date',
      '#title' => $this->t('Date of Birth'),
      '#required' => TRUE,
      '#attributes' => [
        'max' => '2025-08-22T13:09',
      ],
    ];
    $form['aadhar'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Aadhaar Number'),
      '#required' => TRUE,
      '#maxlength' => 14,
      '#attributes' => [
        //'pattern' => '\d{12}',
        'placeholder' => $this->t('XXXX-XXXX-XXXX'),
      ],
      '#suffix' => '<div id="adhaar_validation_error"></div>',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    $form['#attached']['library'][] = 'user_extend/form_field_validation';
    return $form;
  }

  /**
   * Validates the form input before submission.
   *
   * This function is called after the form is submitted but before the submit handler.
   * It validates the user input data and through the errors on those form elements.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form, used to retrieve values and set validation errors.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validates name field value.
    if (!preg_match('/^[a-zA-Z\s\-]+$/', trim($form_state->getValue('name')))) {
      $form_state->setErrorByName('name', $this->t('Name should not be empty.'));
    }
    // Validates phone number field value.
    if (!preg_match('/^(0|91)?[6-9][0-9]{9}$/', trim($form_state->getValue('phone')))) {
      $form_state->setErrorByName('phone', $this->t('Please enter valid phone number'));
    }
    // Validates aadhar number field value.
    if (!preg_match("/^[2-9]{1}[0-9]{3}-[0-9]{4}-[0-9]{4}$/", trim($form_state->getValue('aadhar')))) {
      $form_state->setErrorByName('aadhar', $this->t('Aadhaar number must have 12 numbers in
      XXXX-XXXX-XXXX format and first digit sholuld be 2-9.'));
    }
    // Validates DOB field value.
    $dob_value = trim($form_state->getValue('dob'));
    if (empty(trim($form_state->getValue('dob')))) {
      $form_state->setErrorByName('dob', $this->t('Please select DOB.'));
    }
    else {
      $today = new \DateTime();
      $dob = new \DateTime($dob_value);
      $interval = $today->diff($dob);

      if ($interval->y < 18) {
        $form_state->setErrorByName('dob', $this->t('DOB should be 18 years less than today.'));
      }
    }

    // Validate email format.
    $email = trim($form_state->getValue('email'));
    if (!$this->checkValidEmail($email)) {
      $form_state->setErrorByName('email', $this->t('Please enter a valid email address.'));
    }
    // Check email is already exists.
    if ($this->emailAlreadyExists($email)) {
      $form_state->setErrorByName('email', $this->t('This email is already registered.'));
    }
  }

  
  /**
   * Handles form submission.
   *
   * This method is called after the form is successfully validated.
   * It processes the submitted data and save data to the database.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form, used to retrieve submitted values.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $email = trim($form_state->getValue('email'));
    $profile = $this->entityTypeManager->getStorage('encryption_profile')->load('pii_profile');
    if ($this->commonUitlity->registerUserWithEmail($email)) {
      // Insert data to database.
      $this->database->insert('user_extend_data')
      ->fields([
        'name' => trim($form_state->getValue('name')),
        'email' => $this->encryptionService->encrypt(trim($form_state->getValue('email')), $profile),
        'phone' => $this->encryptionService->encrypt(trim($form_state->getValue('phone')), $profile),
        'dob' => $form_state->getValue('dob'),
        'aadhar' => $this->encryptionService->encrypt(str_replace('-', '', trim($form_state->getValue('aadhar'))), $profile),
      ])
      ->execute();
      // Show mwssage.
      $this->messenger()->addMessage($this->t('User data submitted successfully.'));
    }
    else {
      $this->messenger()->addError('An error occurred.');
    }
  }

  /**
   * Check email address is valid or not.
   */
  public function checkValidEmail($email) {
    if ($this->emailValidator->isValid($email)) {
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
    $user = $this->database->select('user_extend_data', 'ued')
      ->fields('ued', ['id'])
      ->condition('ued.email', $email)
      ->execute()->fetchField();
    if (empty($user)) {
      return false;
    }
    return true;
  }
}

<?php

namespace Drupal\user_extend\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\common_utility\Service\CommonUtility;
use Drupal\Core\Controller\ControllerBase;

class EmailExistCheckController extends ControllerBase {

  protected $requestStack;
  protected $commonUitlity;

  public function __construct(RequestStack $request_stack, CommonUtility $commonUitlity) {
    $this->requestStack = $request_stack;
    $this->commonUitlity = $commonUitlity;
  }

  public function emailExistCheck() {
    $request = $this->requestStack->getCurrentRequest();
    $email = $request->query->get('email');

    if (!$this->commonUitlity->checkValidEmail($email)) {
      return new JsonResponse(['error' => 'Invalid email format'], 400);
    }
    $exists = $this->commonUitlity->emailAlreadyExists($email);
    return new JsonResponse(['exists' => $exists]);
  }
}

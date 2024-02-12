<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_captcha_mock\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\State\State;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller to mock the captcha validation endpoint.
 */
class MockController extends ControllerBase {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * Constructs a new MockController instance.
   *
   * @param \Drupal\Core\State\State $state
   *   The state service.
   */
  public function __construct(State $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
    );
  }

  /**
   * Mock validation.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response.
   */
  public function validate(): JsonResponse {
    $answer = $this->state->get('captcha_mock_response', 'success');
    if ($answer === 'success') {
      $response = new JsonResponse([
        'status' => 'success',
      ], 200);
    }
    else {
      $response = new JsonResponse([
        'status' => 'error',
        'message' => 'Error answer.',
      ], 400);
    }

    return $response;
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_media\Plugin\Validation\Constraint;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\Component\Serialization\Json;
use Drupal\oe_webtools_media\Plugin\media\Source\WebtoolsInterface;

/**
 * Validates the webtools media constraint.
 */
class WebtoolsMediaConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Constructs a WebtoolsMediaConstraintValidator object.
   *
   * @param \GuzzleHttp\Client $http_client
   *   The http client.
   */
  public function __construct(Client $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  public function validate($value, Constraint $constraint) {
    /** @var \Drupal\Core\Field\FieldItemListInterface $value */
    // Bail out if the source field is empty.
    if ($value->isEmpty()) {
      return;
    }

    /** @var \Drupal\media\MediaInterface $media */
    $media = $value->getEntity();

    /** @var \Drupal\oe_webtools_media\Plugin\media\Source\WebtoolsInterface $source */
    $source = $media->getSource();

    if (!($source instanceof WebtoolsInterface)) {
      throw new \LogicException('Media source must implement ' . WebtoolsInterface::class);
    }

    // Get widget types.
    $widget_types = $source->getWidgetTypes();

    // Decode the snippet.
    $snippet = Json::decode($value->first()->value);
    if ($snippet === NULL) {
      // The JSON is invalid, the json_field module has already shown a
      // validation error.
      return;
    }

    // If it's a WCLOUD style service, assert its correct and try to fetch the
    // actual snippet for validation.
    if (!empty($snippet['utility']) && $snippet['utility'] === 'wcloud') {
      $snippet = $this->parseWcloud($snippet, $constraint);
      if (empty($snippet)) {
        return;
      }
    }

    // Add violation in case incorrect services.
    $services = $widget_types[$constraint->widgetType]['services'] ?? [$widget_types[$constraint->widgetType]['service']];
    if (empty($snippet['service']) || (!empty($services) && !in_array($snippet['service'], $services))) {
      $this->context->addViolation($constraint->message, ['%widget_type_name' => $widget_types[$constraint->widgetType]['name']]);
    }

    // Add violation in case blacklisted services.
    if (!empty($snippet['service']) && in_array($snippet['service'], $widget_types[$constraint->widgetType]['blacklist'])) {
      // Add violation in case of blacklisted services.
      $this->context->addViolation($constraint->blacklistMessage);
    }
  }

  /**
   * Attempts to parse and validate a WCLOUD snippet.
   *
   * @param array $snippet
   *   The snippet to ve parsed and validated.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint object.
   *
   * @return array
   *   The parsed snippet if validation passed or an empty array otherwise.
   */
  protected function parseWcloud(array $snippet, Constraint $constraint): array {
    // Assert if the URL property is set and is valid.
    if (empty($snippet['url']) || !UrlHelper::isValid($snippet['url'], TRUE)) {
      $this->context->addViolation($constraint->incorrectUrlMessage);
      return [];
    }
    // Assert that the URL is in the europa.eu domain.
    if (substr(parse_url($snippet['url'], PHP_URL_HOST), -strlen('europa.eu')) !== 'europa.eu') {
      $this->context->addViolation($constraint->incorrectUrlDomainMessage);
      return [];
    }

    // Try to fetch and parse the response.
    try {
      $request = $this->httpClient->get($snippet['url']);
      $wcloud_content = $request->getBody()->getContents();
      $wcloud_snippet = Json::decode($wcloud_content);
    }
    catch (\Exception $exception) {
      $this->context->addViolation($constraint->inaccessibleUrlMessage);
      return [];
    }
    // Assert if parsing the response triggered any errors.
    if (json_last_error() !== JSON_ERROR_NONE) {
      $this->context->addViolation($constraint->incorrectUrlContentMessage);
      return [];
    }
    if (empty($wcloud_snippet)) {
      $this->context->addViolation($constraint->incorrectUrlContentMessage);
    }
    return $wcloud_snippet;
  }

}

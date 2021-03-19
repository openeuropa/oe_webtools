<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_media\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\Component\Serialization\Json;
use Drupal\oe_webtools_media\Plugin\media\Source\WebtoolsInterface;

/**
 * Validates the webtools media constraint.
 */
class WebtoolsMediaConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
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

    // Add violation in case incorrect services.
    $services = $widget_types[$constraint->widgetType]['services'] ?? [$widget_types[$constraint->widgetType]['service']];
    if (!in_array($snippet['service'], $services)) {
      $this->context->addViolation($constraint->message, ['%widget_type_name' => $widget_types[$constraint->widgetType]['name']]);
    }
  }

}

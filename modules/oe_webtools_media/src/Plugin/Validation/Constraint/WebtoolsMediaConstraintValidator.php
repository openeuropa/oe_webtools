<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_media\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\Component\Serialization\Json;

/**
 * Validates the webtools media constraint.
 */
class WebtoolsMediaConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    $data = Json::decode($value->getValue()[0]['value']);
    if (!isset($data['service']) || $data['service'] != $constraint->widgetType) {
      $this->context->addViolation($constraint->message, ['%widgetType' => $constraint->widgetType]);
    }
  }

}

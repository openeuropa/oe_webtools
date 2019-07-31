<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_media\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\Component\Serialization\Json;
use Drupal\media\MediaSourceInterface;

/**
 * Validates the webtools media constraint.
 */
class WebtoolsMediaConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    /** @var \Drupal\media\MediaInterface $media */
    $media = $value->getEntity();

    /** @var \Drupal\media\MediaSourceInterface $source */
    $source = $media->getSource();

    if (!($source instanceof MediaSourceInterface)) {
      throw new \LogicException('Media source must implement ' . MediaSourceInterface::class);
    }

    $snippet = Json::decode($source->getSourceFieldValue($media));
    if (!isset($snippet['service']) || $snippet['service'] !== $constraint->widgetType) {
      $this->context->addViolation($constraint->message, ['%widgetType' => $constraint->widgetType]);
    }
  }

}

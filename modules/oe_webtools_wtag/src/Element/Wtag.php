<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_wtag\Element;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\rdf_skos\Entity\ConceptInterface;

/**
 * Wtag form element integrating the Webtools Wtag service.
 *
 * It extends the original EntityAutocomplete form element and can reference
 * only SKOS Concept entities.
 *
 * @FormElement("oe_webtools_wtag")
 */
class Wtag extends EntityAutocomplete {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();

    $class = static::class;
    $info['#process'][] = [$class, 'processWtag'];

    // Apply default form element properties.
    $info['#target_type'] = 'skos_concept';
    $info['#tags'] = TRUE;
    $info['#theme'] = 'oe_webtools_wtag';
    $info['#modal_title'] = $this->t('Wtag');
    $info['#modal_description'] = $this->t('Use the search field or the tree view to find and add one or more tags to describe the subject of your page.');

    return $info;
  }

  /**
   * Process callback for the element.
   *
   * @param array $element
   *   The form element to process.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The form element.
   */
  public static function processWtag(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $wtag_json = [
      'service' => 'wtag',
      'target' => '#' . $element['#id'],
      'title' => $element['#modal_title'],
      'description' => $element['#modal_description'],
    ];

    $element['#wtag'] = Markup::create(Json::encode($wtag_json));
    $element['#attached']['library'][] = 'oe_webtools/drupal.webtools-smartloader';
    unset($element['#attributes']['data-autocomplete-path']);
    $element['#attributes']['maxlength'] = '100000000';
    $element['#maxlength'] = '100000000';

    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    // Process the #default_value property. In this situation, we need to
    // transform the entity objects into the JSON object expected by Webtols.
    if ($input === FALSE && isset($element['#default_value']) && $element['#process_default_value']) {
      if (is_array($element['#default_value']) && $element['#tags'] !== TRUE) {
        throw new \InvalidArgumentException('The #default_value property is an array but the form element does not allow multiple values.');
      }
      elseif (!empty($element['#default_value']) && !is_array($element['#default_value'])) {
        // Convert the default value into an array for easier processing in
        // static::getEntityLabels().
        $element['#default_value'] = [$element['#default_value']];
      }

      if ($element['#default_value']) {
        if (!(reset($element['#default_value']) instanceof ConceptInterface)) {
          throw new \InvalidArgumentException('The #default_value property has to be in the form of SKOS Concept entities.');
        }

        return static::extractValueJson($element['#default_value']);
      }
    }

    // Process the submitted value. In this case, we need to defer back to the
    // parent class to perform the validation and submission of the values in
    // the default entity autocomplete format: label (id).
    if ($input !== FALSE && $input !== "") {
      $values = json_decode($input, TRUE);
      if (!is_array($values)) {
        throw new \InvalidArgumentException('The submitted values are not in the correct format.');
      }

      if (empty($values)) {
        return '';
      }

      $entities = \Drupal::entityTypeManager()->getStorage($element['#target_type'])->loadMultiple(array_keys($values));
      return static::getEntityLabels($entities);
    }
  }

  /**
   * Extracts the JSON from an array of default entities.
   *
   * @param array $value
   *   The submitted value.
   *
   * @return string
   *   The JSON object.
   */
  public static function extractValueJson(array $value): string {
    $return = [];
    foreach ($value as $entity) {
      $return[$entity->id()] = $entity->label();
    }

    return json_encode($return, JSON_UNESCAPED_SLASHES);
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_wtag\Element;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Textfield;
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
class Wtag extends Textfield {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();

    $class = static::class;
    $info['#process'][] = [$class, 'processWtag'];
    $info['#element_validate'] = [[$class, 'validateWtag']];
    $info['#validate_reference'] = TRUE;
    $info['#process_default_value'] = TRUE;
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
   * Form element validation handler for wtag elements.
   */
  public static function validateWtag(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $value = NULL;

    if (!empty($element['#value'])) {
      $entities = static::jsonToEntities($element['#value']);
      if (!$entities) {
        $form_state->setValueForElement($element, $value);
        return;
      }

      // Validate that they are both Skos Concepts in DET.
      foreach ($entities as $entity) {
        if (!$entity instanceof ConceptInterface || !in_array('http://data.europa.eu/uxp/det', array_column($entity->get('in_scheme')->getValue(), 'target_id'))) {
          $form_state->setError($element, t('The referenced entity (%id) is not valid.', ['%id' => $entity->id()]));
          continue;
        }

        $value[] = [
          'target_id' => $entity->id(),
        ];
      }
    }

    $form_state->setValueForElement($element, $value);
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
      if (!empty($element['#default_value']) && !is_array($element['#default_value'])) {
        // Convert the default value into an array for easier processing in
        // static::getEntityLabels().
        $element['#default_value'] = [$element['#default_value']];
      }

      if ($element['#default_value']) {
        if (!(reset($element['#default_value']) instanceof ConceptInterface)) {
          throw new \InvalidArgumentException('The #default_value property has to be in the form of SKOS Concept entities.');
        }

        return static::entitiesToJson($element['#default_value']);
      }
    }

    // Process the submitted value.
    if ($input !== FALSE && $input !== "") {
      $entities = static::jsonToEntities($input);

      if (!$entities) {
        return '';
      }

      return static::entitiesToJson($entities);
    }
  }

  /**
   * Turns an array of entities to their JSON representation.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $entities
   *   The entities.
   *
   * @return string
   *   The JSON object.
   */
  public static function entitiesToJson(array $entities): string {
    $return = [];
    foreach ($entities as $entity) {
      $return[$entity->id()] = $entity->label();
    }

    return json_encode($return, JSON_UNESCAPED_SLASHES);
  }

  /**
   * Turns a JSON into the entities.
   *
   * @param string $json
   *   The JSON string.
   *
   * @return string
   *   The entities.
   */
  public static function jsonToEntities(string $json): array {
    $values = json_decode($json, TRUE);
    if (!is_array($values)) {
      throw new \InvalidArgumentException('The submitted values are not in the correct format.');
    }

    if (empty($values)) {
      return [];
    }

    return \Drupal::entityTypeManager()->getStorage('skos_concept')->loadMultiple(array_keys($values));
  }

}

<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_wtag\Element;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Textfield;
use Drupal\Core\Render\Markup;
use Drupal\rdf_skos\Entity\Concept;
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
    $info['#process_default_value'] = FALSE;
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
  public static function processWtag(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    $wtag_child_id = $element['#id'] . '--wtag';

    $wtag_json = [
      'service' => 'wtag',
      'target' => '#' . $wtag_child_id,
      'title' => $element['#modal_title'],
      'description' => $element['#modal_description'],
    ];

    $entity_json = '';
    $default_entities = [];
    if (!empty($element['#default_value'])) {
      $entities = is_array($element['#default_value'])
        ? $element['#default_value']
        : [$element['#default_value']];
      $entity_json = static::entitiesToJson($entities);
      $default_entities = $entities;
    }

    // Main Wtag element.
    $element['wtag'] = [
      '#type' => 'textfield',
      '#theme' => 'oe_webtools_wtag',
      '#title' => $element['#title'] ?? '',
      '#description' => $element['#description'] ?? '',
      '#required' => $element['#required'] ?? FALSE,
      '#default_value' => $entity_json,
      '#maxlength' => 100000000,
      '#id' => $wtag_child_id,
      '#attributes' => ['maxlength' => '100000000'] + ($element['#attributes'] ?? []),
      '#wtag' => Markup::create(Json::encode($wtag_json)),
      '#attached' => ['library' => ['oe_webtools/drupal.webtools-smartloader']],
      '#value_callback' => [static::class, 'valueCallbackWtag'],
      '#process_default_value' => TRUE,
    ];

    // Fallback element.
    $element['fallback'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'skos_concept',
      '#selection_handler' => 'default:skos_concept',
      '#selection_settings' => [
        'concept_schemes' => ['http://data.europa.eu/uxp/det'],
      ],
      '#tags' => TRUE,
      '#maxlength' => 1200,
      '#default_value' => $default_entities ?: NULL,
      '#process_default_value' => TRUE,
      '#wrapper_attributes' => ['class' => ['wtag-fallback']],
    ];

    // Hidden input that determines via JS how the value is handled: via the
    // normal wtag or fallback.
    $element['input_mode'] = [
      '#type' => 'hidden',
      '#default_value' => 'wtag',
      '#attributes' => ['data-wtag-input-mode' => ''],
    ];

    unset($element['#maxlength']);
    $element['#theme_wrappers'] = ['container'];
    $element['#attributes']['class'][] = 'wtag-element-wrapper';
    $element['#attributes']['data-wtag-id'] = $wtag_child_id;

    $element['#attached']['library'][] = 'oe_webtools_wtag/oe_webtools_wtag.fallback';

    unset($element['#theme']);

    return $element;
  }

  /**
   * Form element validation handler for wtag elements.
   */
  public static function validateWtag(array &$element, FormStateInterface $form_state, array &$complete_form): void {
    $input_mode = $element['input_mode']['#value'] ?? 'wtag';

    if ($input_mode === 'fallback') {
      $result = $form_state->getValue($element['fallback']['#parents']);
      $form_state->setValueForElement($element, $result);
      return;
    }

    // Otherwise it's the normal Wtag element.
    $value = NULL;

    if (!empty($element['wtag']['#value'])) {
      try {
        $entities = static::jsonToEntities($element['wtag']['#value']);
      }
      catch (\InvalidArgumentException $e) {
        $form_state->setError($element, t('The submitted tag data is not in the correct format.'));
        $form_state->setValueForElement($element, NULL);
        return;
      }

      if ($entities) {
        foreach ($entities as $entity) {
          if (!$entity instanceof ConceptInterface || !in_array('http://data.europa.eu/uxp/det', array_column($entity->get('in_scheme')->getValue(), 'target_id'))) {
            $form_state->setError($element, t('The referenced entity (%id) is not valid.', ['%id' => $entity->id()]));
            continue;
          }
          $value[] = ['target_id' => $entity->id()];
        }
      }
    }

    $form_state->setValueForElement($element, $value);
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE) {
      // Initial load, not a form submission.
      return '';
    }

    // Return the relevant child value as a string so that Drupal's required
    // validation can inspect it, while avoiding the raw array fallback.
    $input_mode = $input['input_mode'] ?? 'wtag';

    if ($input_mode === 'fallback') {
      return !empty($input['fallback']) ? $input['fallback'] : '';
    }

    // Wtag mode: return the JSON string from the child textarea.
    return $input['wtag'] ?? '';
  }

  /**
   * Value callback for the wtag child element.
   *
   * We need this callback in case the field is element is marked as required.
   * In this case, the value cannot be empty so we need to fish it out of the
   * fallback.
   *
   * @param array $element
   *   The form element.
   * @param mixed $input
   *   The submitted input, or FALSE if this is the initial load.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return string
   *   The processed value.
   */
  public static function valueCallbackWtag(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE && isset($element['#default_value']) && $element['#process_default_value']) {
      return $element['#default_value'];
    }
    if ($input !== FALSE && $input !== '') {
      try {
        $entities = static::jsonToEntities($input);
      }
      catch (\InvalidArgumentException $e) {
        // Return the raw input so validateWtag can detect and report it.
        return $input;
      }
      if (!$entities) {
        return '';
      }
      return static::entitiesToJson($entities);
    }

    $array_parents = NestedArray::getValue($form_state->getCompleteForm(), array_slice($element['#array_parents'], 0, 2));
    if (!isset($array_parents['target_id']['#parents'])) {
      return '';
    }
    $parents = $array_parents['target_id']['#parents'];
    $fallback = $form_state->getValue($parents);
    if (!$fallback) {
      return '';
    }

    $tags = Tags::explode($fallback);
    $entities = [];
    foreach ($tags as $tag) {
      $entities[] = Concept::load(EntityAutocomplete::extractEntityIdFromAutocompleteInput($tag));
    }

    return static::entitiesToJson($entities);
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

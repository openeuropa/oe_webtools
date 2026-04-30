<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_wepick_icons\Element;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Textfield;
use Drupal\Core\Render\Markup;

/**
 * WePick Icons form element integrating the Webtools pickicons service.
 *
 * @FormElement("oe_webtools_wepick_icons")
 */
class WePickIcons extends Textfield {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();

    $class = static::class;
    $info['#process'][] = [$class, 'processWePickIcons'];
    $info['#element_validate'] = [[$class, 'validateWePickIcons']];
    $info['#theme'] = 'oe_webtools_wepick_icons';
    $info['#modal_title'] = $this->t('Icon picker');
    $info['#include'] = [];
    $info['#exclude'] = [];

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
  public static function processWePickIcons(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $config = [
      'service' => 'pickicons',
      'target' => '#' . $element['#id'],
    ];

    if (!empty($element['#modal_title'])) {
      $config['title'] = (string) $element['#modal_title'];
    }

    if (!empty($element['#include'])) {
      $config['include'] = array_filter($element['#include']);
    }

    if (!empty($element['#exclude'])) {
      $config['exclude'] = array_filter($element['#exclude']);
    }

    $element['#wepick_icons'] = Markup::create(Json::encode($config));
    $element['#attached']['library'][] = 'oe_webtools/drupal.webtools-smartloader';
    unset($element['#attributes']['data-autocomplete-path']);
    $element['#attributes']['maxlength'] = '100000000';
    $element['#maxlength'] = '100000000';

    return $element;
  }

  /**
   * Form element validation handler for WePick Icons elements.
   */
  public static function validateWePickIcons(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $value = NULL;

    if (!empty($element['#value'])) {
      $decoded = json_decode($element['#value'], TRUE);

      if (!is_array($decoded)) {
        $form_state->setError($element, t('The submitted icon value is not in the correct format.'));
        $form_state->setValueForElement($element, $value);
        return;
      }

      if (empty($decoded['name']) || !is_string($decoded['name']) || empty($decoded['family']) || !is_string($decoded['family'])) {
        $form_state->setError($element, t('The selected icon must have a valid name and family.'));
        $form_state->setValueForElement($element, $value);
        return;
      }

      $value = [
        'icon_name' => $decoded['name'],
        'icon_family' => $decoded['family'],
      ];
    }

    $form_state->setValueForElement($element, $value);
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    // Process the #default_value property.
    if ($input === FALSE && isset($element['#default_value'])) {
      $default = $element['#default_value'];

      if (is_array($default) && !empty($default['icon_name']) && !empty($default['icon_family'])) {
        return json_encode([
          'name' => $default['icon_name'],
          'family' => $default['icon_family'],
        ], JSON_UNESCAPED_SLASHES);
      }

      return '';
    }

    // Process the submitted value.
    if ($input !== FALSE && $input !== '') {
      return $input;
    }

    return '';
  }

}

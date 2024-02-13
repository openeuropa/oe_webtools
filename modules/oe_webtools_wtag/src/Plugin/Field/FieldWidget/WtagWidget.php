<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_wtag\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'oe_webtools_wtag_autocomplete' widget.
 *
 * @FieldWidget(
 *    id = "oe_webtools_wtag",
 *    label = @Translation("Webtools Wtag"),
 *    description = @Translation("A Webtools Wtag widget that allows the selection of SKOS Concepts in a modal window."),
 *    field_types = {
 *      "skos_concept_entity_reference"
 *    }
 *  )
 */
class WtagWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'modal_title' => t('Wtag'),
      'modal_description' => t('Use the search field or the tree view to find and add one or more tags.'),
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['modal_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Modal title'),
      '#default_value' => $this->getSetting('modal_title'),
      '#description' => $this->t('The title of the modal.'),
    ];
    $element['modal_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Modal description'),
      '#default_value' => $this->getSetting('modal_description'),
      '#description' => $this->t('The introductory text on the modal.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Modal title: @title', ['@title' => $this->getSetting('modal_title')]);
    $summary[] = $this->t('Modal description: @description', ['@description' => $this->getSetting('modal_description')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This widget is only applicable to unlimited cardinality SKOS Concept
    // reference fields that reference the DET vocabulary.
    if ($field_definition->getFieldStorageDefinition()->getCardinality() !== FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      return FALSE;
    }

    $concept_schemes = $field_definition->getSetting('handler_settings')['concept_schemes'];
    if (count($concept_schemes) > 1 || empty($concept_schemes)) {
      return FALSE;
    }

    if ($concept_schemes[0] !== 'http://data.europa.eu/uxp/det') {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $referenced_entities = $items->referencedEntities();

    $element += [
      '#type' => 'oe_webtools_wtag',
      '#modal_title' => $this->getSetting('modal_title'),
      '#modal_description' => $this->getSetting('modal_description'),
      '#default_value' => $referenced_entities,
    ];

    return ['target_id' => $element];
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $error, array $form, FormStateInterface $form_state) {
    return $element['target_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    return is_array($values['target_id']) ? $values['target_id'] : [];
  }

  /**
   * {@inheritdoc}
   */
  protected function handlesMultipleValues() {
    return TRUE;
  }

}

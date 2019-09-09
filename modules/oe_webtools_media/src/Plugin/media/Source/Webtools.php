<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_media\Plugin\media\Source;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media\MediaSourceBase;
use Drupal\media\MediaTypeInterface;

/**
 * Provides a media source plugin for Webtools resources.
 *
 * @see \Drupal\file\FileInterface
 *
 * @MediaSource(
 *   id = "webtools",
 *   label = @Translation("Webtools"),
 *   description = @Translation("Media webtools plugin."),
 *   allowed_field_types = {"json"}
 * )
 */
class Webtools extends MediaSourceBase implements WebtoolsInterface {

  /**
   * {@inheritdoc}
   */
  public function getMetadataAttributes() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceFieldConstraints() {
    return [
      'ValidWebtoolsMedia' => [
        'widgetType' => $this->configuration['widget_type'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['widget_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Widget type'),
      '#options' => array_combine(array_keys($this->getWidgetTypes()), array_column($this->getWidgetTypes(), 'name')),
      '#default_value' => $this->configuration['widget_type'],
      '#description' => $this->t('Select the webtools widget type.'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'widget_type' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function createSourceField(MediaTypeInterface $type) {
    $label = (string) $this->t('Webtools @widget_type_name snippet', [
      '@widget_type_name' => $this->getWidgetTypes()[$this->configuration['widget_type']]['name'],
    ]);
    return parent::createSourceField($type)
      ->set('label', $label)
      ->set('description', $this->t('Enter the snippet without the script tag.'));
  }

  /**
   * {@inheritdoc}
   */
  public function prepareViewDisplay(MediaTypeInterface $type, EntityViewDisplayInterface $display) {
    $display->setComponent($this->getSourceFieldDefinition($type)->getName(), [
      'type' => 'webtools_snippet',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetTypes() {
    return [
      'chart' => [
        'name' => $this->t('Chart'),
        'service' => 'charts',
      ],
      'map' => [
        'name' => $this->t('Map'),
        'service' => 'map',
      ],
      'social_feed' => [
        'name' => $this->t('Social feed'),
        'service' => 'smk',
      ],
    ];
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_media\Plugin\media\Source;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\media\MediaInterface;
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
  public function getMetadata(MediaInterface $media, $attribute_name) {
    if ($attribute_name === 'thumbnail_uri') {
      return $this->getThumbnail() ?: parent::getMetadata($media, $attribute_name);
    }
    return parent::getMetadata($media, $attribute_name);
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

    $generator_link = Link::fromTextAndUrl(
      $this->t('Webtools wizard'),
      Url::fromUri('https://europa.eu/webtools/mgmt/wizard/')
    )->toString();
    // The opwidget type has to be created on the op website.
    if ($this->configuration['widget_type'] === 'opwidget') {
      $generator_link = Link::fromTextAndUrl(
        $this->t('OP Website'),
        Url::fromUri('https://op.europa.eu/en/my-widgets')
      )->toString();
    }

    return parent::createSourceField($type)
      ->set('label', $label)
      ->set('description', $this->t('Enter the snippet without the script tag. Snippets can be generated in @generator_link.', [
        '@generator_link' => $generator_link,
      ]));
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
        // @deprecated Use services key instead.
        'service' => 'charts',
        'services' => ['charts', 'chart', 'racing'],
        'default_thumbnail' => 'charts-embed-no-bg.png',
      ],
      'map' => [
        'name' => $this->t('Map'),
        // @deprecated Use services key instead.
        'service' => '',
        'services' => ['map'],
        'default_thumbnail' => 'maps-embed-no-bg.png',
      ],
      'social_feed' => [
        'name' => $this->t('Social feed'),
        // @deprecated Use services key instead.
        'service' => '',
        'services' => ['smk'],
        'default_thumbnail' => 'twitter-embed-no-bg.png',
      ],
      'opwidget' => [
        'name' => $this->t('OP Publication list'),
        // @deprecated Use services key instead.
        'service' => '',
        'services' => ['opwidget'],
        'default_thumbnail' => 'generic.png',
      ],
    ];
  }

  /**
   * Gets the thumbnail image URI based on widget type.
   *
   * @return string
   *   URI of the thumbnail.
   */
  protected function getThumbnail(): string {
    $icon_base = $this->configFactory->get('media.settings')->get('icon_base_uri');
    $widget_type = $this->configuration['widget_type'];
    return $icon_base . '/' . $this->getWidgetTypes()[$widget_type]['default_thumbnail'];
  }

}

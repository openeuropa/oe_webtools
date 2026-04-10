<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_wepick_icons\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'oe_webtools_wepick_icons' field type.
 *
 * @FieldType(
 *   id = "oe_webtools_wepick_icons",
 *   label = @Translation("Webtools WePick Icons"),
 *   description = @Translation("Stores an icon selection from the Webtools pickicons service."),
 *   default_widget = "oe_webtools_wepick_icons",
 * )
 */
class WePickIconsItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'icon_name' => [
          'type' => 'varchar',
          'length' => 255,
        ],
        'icon_family' => [
          'type' => 'varchar',
          'length' => 255,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['icon_name'] = DataDefinition::create('string')
      ->setLabel(t('Icon name'))
      ->setRequired(TRUE);

    $properties['icon_family'] = DataDefinition::create('string')
      ->setLabel(t('Icon family'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $name = $this->get('icon_name')->getValue();
    $family = $this->get('icon_family')->getValue();
    return ($name === NULL || $name === '') && ($family === NULL || $family === '');
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'icon_name';
  }

}

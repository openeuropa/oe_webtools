<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_media\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Serialization\Json;
use Drupal\json_field\JsonMarkup;

/**
 * Plugin implementation of the 'webtools_snippet' formatter.
 *
 * @FieldFormatter(
 *   id = "webtools_snippet",
 *   label = @Translation("Webtools snippet"),
 *   field_types = {
 *     "json",
 *   }
 * )
 */
class WebtoolsSnippetFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#value' => JsonMarkup::create(Json::encode(Json::decode($item->get('value')->getValue()))),
        '#attributes' => ['type' => 'application/json'],
      ];
    }

    if ($element) {
      $element['#attached'] = [
        'library' => ['oe_webtools/drupal.webtools-smartloader'],
      ];
    }

    return $element;
  }

}

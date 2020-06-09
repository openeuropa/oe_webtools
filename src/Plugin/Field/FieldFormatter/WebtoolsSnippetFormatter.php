<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools\Plugin\Field\FieldFormatter;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Render\Markup;

/**
 * Plugin implementation of the 'webtools_snippet' formatter.
 *
 * @FieldFormatter(
 *   id = "webtools_snippet",
 *   label = @Translation("Webtools snippet"),
 *   field_types = {
 *     "json",
 *   },
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
        // We need to properly escape the json content before outputting in the
        // page. We cannot use twig escaping mechanism as it will use html
        // entities. Json::encode takes care of it.
        '#value' => Markup::create(Json::encode(Json::decode($item->get('value')->getValue()))),
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

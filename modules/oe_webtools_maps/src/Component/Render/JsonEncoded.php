<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_maps\Component\Render;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Serialization\Json;

/**
 * Formats a JSON array.
 */
class JsonEncoded implements MarkupInterface {

  /**
   * The json array to serialize and render.
   *
   * @var array
   */
  protected $json;

  /**
   * Constructs a JsonEncoded object.
   *
   * @param array $json
   *   The json array to serialize and render.
   */
  public function __construct(array $json) {
    $this->json = $json;
  }

  /**
   * Gets the json array.
   *
   * @return array
   *   The json array to serialize and render.
   */
  public function getJson(): array {
    return $this->json;
  }

  /**
   * Sets the json array.
   *
   * @param array $json
   *   The json array to serialize and render.
   */
  public function setJson(array $json): void {
    $this->json = $json;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return $this->jsonSerialize();
  }

  /**
   * {@inheritdoc}
   */
  public function jsonSerialize() {
    return Json::encode($this->json);
  }

}

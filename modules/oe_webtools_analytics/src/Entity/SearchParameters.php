<?php

declare(strict_types = 1);

/**
 * Contains the class that will represent the search field on Event listener.
 *
 * @see https://webgate.ec.europa.eu/fpfis/wikis/pages/viewpage.action?spaceKey=webtools&title=Piwik
 */

namespace Drupal\oe_webtools_analytics\Entity;

use JsonSerializable;

/**
 * Class SearchParameters.
 *
 * @package Drupal\oe_webtools_analytics\Entity
 */
class SearchParameters implements JsonSerializable, SearchParametersInterface {
  /**
   * Keyword searched (mandatory).
   *
   * @var string
   */
  private $keyword;

  /**
   * Category of the search (optional).
   *
   * @var string
   */
  private $category;

  /**
   * Count of search results (optional).
   *
   * @var int
   *   An integer indicating how many results were found.
   */
  private $count;

  /**
   * {@inheritdoc}
   */
  public function setKeyword(string $keyword): void {
    $this->keyword = $keyword;
  }

  /**
   * {@inheritdoc}
   */
  public function setCategory($category): void {
    $this->category = $category;
  }

  /**
   * {@inheritdoc}
   */
  public function setCount(int $count): void {
    $this->count = $count;
  }

  /**
   * {@inheritdoc}
   */
  public function getKeyword(): string {
    return $this->keyword;
  }

  /**
   * {@inheritdoc}
   */
  public function getCategory(): string {
    return $this->category;
  }

  /**
   * {@inheritdoc}
   */
  public function getCount(): int {
    return $this->count;
  }

  /**
   * {@inheritdoc}
   */
  public function isSetKeyword(): bool {
    return !empty($this->keyword);
  }

  /**
   * {@inheritdoc}
   */
  public function jsonSerialize() {
    $search = [
      'keyword' => $this->keyword,
      'category' => $this->category,
      'count' => $this->count,
    ];

    return array_filter($search);
  }

}

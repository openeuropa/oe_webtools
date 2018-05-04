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
class SearchParameters implements jsonserializable {
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
   * Sets the search keyword.
   *
   * @param string $keyword
   *   A mandatory string value.
   */
  public function setKeyword(string $keyword): void {
    $this->keyword = $keyword;
  }

  /**
   * Sets search category.
   *
   * @param string $category
   *   An optional string value.
   */
  public function setCategory($category): void {
    $this->category = $category;
  }

  /**
   * Sets search results count.
   *
   * @param int $count
   *   An optional number value.
   */
  public function setCount(int $count): void {
    $this->count = $count;
  }

  /**
   * Check whether or not the keyword has been set.
   *
   * @return bool
   *   True in case the variable has been set otherwise false.
   */
  public function isSetKeyword() {
    return !empty($this->keyword);
  }

  /**
   * Serialize the data with custom indexes.
   *
   * @return array|mixed
   *   An array of above parameters.
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

<?php

declare(strict_types = 1);

/**
 * Contains the class that will represent the search field on Event listener.
 *
 * @see https://webgate.ec.europa.eu/fpfis/wikis/pages/viewpage.action?spaceKey=webtools&title=Piwik
 */

namespace Drupal\oe_webtools_analytics\Search;

/**
 * Class SearchParameters.
 *
 * @package Drupal\oe_webtools_analytics\Search
 */
interface SearchParametersInterface extends \JsonSerializable {

  /**
   * Sets the search keyword.
   *
   * @param string $keyword
   *   A mandatory string value.
   */
  public function setKeyword(string $keyword): void;

  /**
   * Sets search category.
   *
   * @param string $category
   *   An optional string value.
   */
  public function setCategory($category): void;

  /**
   * Sets search results count.
   *
   * @param int $count
   *   An optional number value.
   */
  public function setCount(int $count): void;

  /**
   * The search keyword.
   *
   * @return string
   *   A string with the value searched.
   */
  public function getKeyword(): string;

  /**
   * Get the search category parameter.
   *
   * @return string
   *   The search category.
   */
  public function getCategory(): string;

  /**
   * Get the search count parameter.
   *
   * @return int
   *   The number of results found.
   */
  public function getCount(): int;

  /**
   * Check whether or not the keyword has been set.
   *
   * @return bool
   *   True in case the variable has been set otherwise false.
   */
  public function isSetKeyword(): bool;

}

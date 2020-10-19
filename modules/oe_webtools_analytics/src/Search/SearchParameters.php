<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics\Search;

/**
 * Provides the search parameters.
 *
 * @see https://webgate.ec.europa.eu/fpfis/wikis/pages/viewpage.action?spaceKey=webtools&title=Piwik
 *
 * @package Drupal\oe_webtools_analytics\Search
 */
class SearchParameters implements SearchParametersInterface {
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
   * SearchParameters constructor.
   *
   * @param string $category
   *   The category.
   * @param string $keyword
   *   The keyword.
   * @param int $count
   *   The search count.
   */
  public function __construct(string $category = '', string $keyword = '', int $count = 0) {
    $this->setCategory($category);
    $this->setKeyword($keyword);
    $this->setCount($count);
  }

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
    return NULL !== $this->getKeyword();
  }

  /**
   * {@inheritdoc}
   */
  public function jsonSerialize() {
    return array_filter([
      'keyword' => $this->getKeyword(),
      'category' => $this->getCategory(),
      'count' => $this->getCount(),
    ]);
  }

}

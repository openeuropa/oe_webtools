<?php

/**
 * @file
 * Contains \OpenEuropaWebtoolsMapsSubContext.
 */

declare(strict_types = 1);

use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Behat step definitions for testing Webtools Maps.
 */
class OpenEuropaWebtoolsMapsSubContext extends DrupalSubContextBase {

  /**
   * Checks that a map centered on the given coordinates is present in the page.
   *
   * @param string $latitude
   *   The latitude for the center map position.
   * @param string $longitude
   *   The longitude for the center map position.
   *
   * @throws \RuntimeException
   *   If the map with the given coordinates was not found in the page.
   *
   * @Then I should see a map centered on latitude :latitude and longitude :longitude
   */
  public function assertMapPresent(string $latitude, string $longitude): void {
    foreach ($this->getWebtoolsMaps() as $data) {
      if (!empty($data->map->center) && count($data->map->center) === 2) {
        $center = $data->map->center;
        if ($center[0] == $longitude && $center[1] == $latitude) {
          // The map was found.
          return;
        }
      }
    }
    throw new \RuntimeException("Map with coordinates $latitude, $longitude was not found in the page.");
  }

  /**
   * Returns an array of JSON data representing Webtools maps.
   *
   * @return object[]
   *   The JSON data representing Webtools maps.
   */
  protected function getWebtoolsMaps(): array {
    $maps = [];

    $xpath = '//script[@type = "application/json"]';
    /** @var \Behat\Mink\Element\NodeElement $element */
    foreach ($this->getSession()->getPage()->findAll('xpath', $xpath) as $element) {
      $data = json_decode($element->getText());

      if (!empty($data) && !empty($data->service) && $data->service === 'map' && !empty($data->map)) {
        $maps[] = $data;
      }
    }

    return $maps;
  }

  /**
   * Checks that map is unavailable on the current page.
   *
   * @throws \RuntimeException
   * If the map is available on the current page.
   *
   * @Then I should not see a map on the page
   */
  public function mapIsNotAvailable(): void {
    foreach ($this->getWebtoolsMaps() as $data) {
      if (empty($data)) {
        // The map was not found.
        return;
      }
    }
    throw new RuntimeException("Map is available on the current page.");
  }

  /**
   * Checks that map is available on the current page.
   *
   * @throws \RuntimeException
   * If the map is unavailable on the current page.
   *
   * @Then I should see a map on the page
   */
  public function mapIsAvailable(): void {
    foreach ($this->getWebtoolsMaps() as $data) {
      if (!empty($data)) {
        // The map was found.
        return;
      }
    }
    throw new RuntimeException("Map is unavailable on the current page.");
  }

}

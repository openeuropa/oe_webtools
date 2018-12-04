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
   * @Then I should see a map centered on latitude :latitude and longitude :longitude
   */
  public function assertMapPresent(string $latitude, string $longitude): void {
    $xpath = '//script[@type = "application/json"]';
    /** @var \Behat\Mink\Element\NodeElement $element */
    foreach ($this->getSession()->getPage()->findAll('xpath', $xpath) as $element) {
      $data = json_decode($element->getText());

      // Skip if this is not a Webtools map.
      if (empty($data) || empty($data->service) || $data->service !== 'map' || empty($data->map)) {
        continue;
      }

      // Check the coordinates.
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

}

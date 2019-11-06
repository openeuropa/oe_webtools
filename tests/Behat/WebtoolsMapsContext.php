<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use PHPUnit\Framework\Assert;

/**
 * Behat step definitions for testing Webtools Maps.
 */
class WebtoolsMapsContext extends RawDrupalContext {

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
   * Checks that a map with a marker with the given data is present.
   *
   * Table format:
   * @codingStandardsIgnoreStart
   * | name        | Digit HQ                           |
   * | description | Rue Belliard 28, Brussels, Belgium |
   * | latitude    | 4.370375                           |
   * | longitude   | 50.842156                          |
   * @codingStandardsIgnoreEnd
   *
   * @param \Behat\Gherkin\Node\TableNode $data
   *   The data for the marker. Should contain one or more of the following
   *   labels: 'name', 'description', 'latitude', 'longitude'.
   *
   * @throws \RuntimeException
   *   If the map with the given marker was not found in the page.
   *
   * @Then I should see the following marker on the map:
   */
  public function assertMarkerPresent(TableNode $data): void {
    $hashed_data = $data->getRowsHash();
    foreach ($this->getWebtoolsMaps() as $map_data) {
      foreach ($hashed_data as $type => $value) {
        switch ($type) {
          case 'name':
            if ($map_data->layers[0]->markers->features[0]->properties->name !== $value) {
              continue 3;
            }
            break;

          case 'description':
            if ($map_data->layers[0]->markers->features[0]->properties->description !== $value) {
              continue 3;
            }
            break;

          case 'latitude':
            if ((string) $map_data->layers[0]->markers->features[0]->geometry->coordinates[1] !== $value) {
              continue 3;
            }
            break;

          case 'longitude':
            if ((string) $map_data->layers[0]->markers->features[0]->geometry->coordinates[0] !== $value) {
              continue 3;
            }
            break;

          default:
            throw new \RuntimeException("Unknown marker data property $type.");
        }
      }
      // The marker was found.
      return;
    }
    throw new \RuntimeException('No map found with a marker that corresponds to the data "' . implode(', ', $hashed_data) . '".');
  }

  /**
   * Checks that there are no maps on the current page.
   *
   * @Then I should not see a(ny) map(s) on the page
   */
  public function assertNoMapPresent(): void {
    Assert::assertEmpty($this->getWebtoolsMaps());
  }

  /**
   * Checks that one or more maps are available on the current page.
   *
   * @param string $count
   *   The number of maps that are expected to be present in the page, or "a"
   *   if we are checking for the presence of any number of maps on the page.
   *
   * @Then /^I should see (a|\d+) map(?:s|) on the page$/
   */
  public function assertMapCount($count): void {
    $maps = $this->getWebtoolsMaps();
    if (is_int($count)) {
      Assert::assertCount($count, $maps);
    }
    else {
      Assert::assertNotEmpty($maps);
    }
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

}

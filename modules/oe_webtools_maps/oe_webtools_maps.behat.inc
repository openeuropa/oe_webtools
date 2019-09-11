<?php

/**
 * @file
 * Contains \OeWebtoolsMapsSubContext.
 */

declare(strict_types = 1);

use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use PHPUnit\Framework\Assert;

/**
 * Behat step definitions for testing Webtools Maps.
 *
 * @deprecated Sub-contexts are deprecated in Drupal Behat Extension 4.0.0 and
 * will be removed in 4.1.0. Class OeWebtoolsMapsSubContext is a subcontext.
 * This logic should be moved to a normal Behat context and
 * loaded via behat.yml.
 * @see \Drupal\Tests\oe_webtools\Behat\WebtoolsMapsContext
 */
class OeWebtoolsMapsSubContext extends DrupalSubContextBase {

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
      Assert::assertNotEmpty($this->getWebtoolsMaps());
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

<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use PHPUnit\Framework\Assert;

/**
 * Behat step definitions for testing Webtools eTrans.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class WebtoolsETransContext extends RawDrupalContext {

  /**
   * The various ways the eTrans link can be rendered.
   */
  protected const RENDER_OPTIONS = ['button', 'icon', 'link'];

  /**
   * Checks that an eTrans element is present on the page.
   *
   * @param string|null $type
   *   The type of eTrans element. Can be either 'button', 'icon' or 'link'.
   *   Omit this parameter to check that any of the possible element types is
   *   present.
   *
   * @throws \RuntimeException
   *   Thrown when the eTrans element was not found.
   *
   * @Then I should see a Webtools eTrans element
   * @Then I should see the Webtools eTrans :type
   */
  public function assertElementPresent(?string $type = ''): void {
    Assert::assertTrue(empty($type) || in_array($type, self::RENDER_OPTIONS), 'Element type should be either "button", "icon" or "link".');
    foreach ($this->getElements() as $data) {
      $types_to_check = $type ? [$type] : self::RENDER_OPTIONS;
      foreach ($types_to_check as $type_to_check) {
        if ($data->renderAs->$type_to_check ?? FALSE) {
          // The element is found.
          return;
        }
      }
    }
    $type = $type ?: 'element';
    throw new \RuntimeException("Webtools eTrans $type was not found in the page.");
  }

  /**
   * Checks that an eTrans element is not present on the page.
   *
   * @param string|null $type
   *   The type of eTrans element. Can be either 'button', 'icon' or 'link'.
   *   Omit this parameter to check that none of the possible element types is
   *   present.
   *
   * @throws \RuntimeException
   *   Thrown when the eTrans element was unexpectedly found.
   *
   * @Then I should not see the Webtools eTrans :type
   * @Then I should not see any Webtools eTrans elements
   */
  public function assertNoElementPresent(?string $type = ''): void {
    Assert::assertTrue(empty($type) || in_array($type, self::RENDER_OPTIONS), 'Element type should be either "button", "icon" or "link."');
    foreach ($this->getElements() as $data) {
      $types_to_check = $type ? [$type] : self::RENDER_OPTIONS;
      foreach ($types_to_check as $type) {
        if ($data->renderAs->$type ?? FALSE) {
          throw new \RuntimeException("Webtools eTrans $type was unexpectedly found in the page.");
        }
      }
    }
  }

  /**
   * Returns an array of JSON data representing Webtools eTrans elements.
   *
   * @return object[]
   *   The JSON data representing Webtools eTrans elements.
   */
  protected function getElements(): array {
    $elements = [];

    $xpath = '//div[contains(concat(" ", normalize-space(@class), " "), " block-oe-webtools-etrans ")]/script[@type="application/json"]';
    /** @var \Behat\Mink\Element\NodeElement $element */
    foreach ($this->getSession()->getPage()->findAll('xpath', $xpath) as $element) {
      $data = json_decode($element->getHtml());

      if (!empty($data) && !empty($data->service) && $data->service === 'etrans') {
        $elements[] = $data;
      }
    }

    return $elements;
  }

}

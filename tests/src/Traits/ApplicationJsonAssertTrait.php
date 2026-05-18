<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools\Traits;

use Drupal\Component\Serialization\Json;

/**
 * Helper methods to deal with asserting page content.
 */
trait ApplicationJsonAssertTrait {

  /**
   * Asserts that the page body contains the application JSON.
   *
   * @param string $json
   *   The application JSON.
   */
  protected function assertBodyContainsApplicationJson(string $json): void {
    $application_json = '<script type="application/json">' . $json . '</script>';
    $actual = $this->getSession()->getPage()->find('css', 'body')->getHtml();
    $message = sprintf('"%s" was not found anywhere in the HTML body.', $application_json);

    $this->assertTrue(stripos($actual, $application_json) !== FALSE, $message);
  }

  /**
   * Asserts that the page has an JSON script matching expected data.
   *
   * Performs a structural comparison after JSON-decoding the script body,
   * so the assertion is robust to key ordering and whitespace.
   *
   * @param array $expected
   *   The expected decoded JSON payload.
   * @param string $css_selector
   *   CSS selector locating the script element. Defaults to the first
   *   application/json script on the page.
   */
  protected function assertApplicationJsonEquals(array $expected, string $css_selector = 'script[type="application/json"]'): void {
    $script = $this->getSession()->getPage()->find('css', $css_selector);
    $this->assertNotNull($script, sprintf('No element matching "%s" was found.', $css_selector));
    $this->assertSame($expected, Json::decode($script->getText()));
  }

}

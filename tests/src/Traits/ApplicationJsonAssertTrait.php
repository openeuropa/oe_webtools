<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools\Traits;

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

}

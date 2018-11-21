<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Drupal\DrupalExtension\Context\MinkContext;
use PHPUnit\Framework\Assert;

/**
 * Behat step definitions related to the oe_webtools_analytics_rules module.
 */
class WebtoolsAnalyticsMinkContext extends MinkContext {

  /**
   * Asserts whether the Webtools Analytics json contains a certain parameter.
   *
   * @param string $parameter
   *   The parameter name.
   * @param string $value
   *   The parameter value.
   *
   * @throws \Exception
   *
   * @Then the page analytics json should contain the parameter :parameter with the value :value
   */
  public function analyticsJsonContainsParameter(string $parameter, string $value): void {
    $scripts = $this->getSession()->getPage()->findAll("css", "script[type=\"application/json\"]");
    $json_found = FALSE;
    /** @var \Behat\Mink\Element\NodeElement $script */
    foreach ($scripts as $script) {
      $json_value = json_decode($script->getText(), TRUE);
      if (isset($json_value['utility']) && $json_value['utility'] == 'piwik') {
        $json_found = TRUE;
        Assert::assertEquals($value, $json_value[$parameter]);
      }
    }
    if (!$json_found) {
      throw new \Exception(sprintf('No analytics json found.'));
    }
  }

  /**
   * Asserts whether the Webtools Analytics json doesn't contain a parameter.
   *
   * @param string $parameter
   *   The parameter name.
   *
   * @throws \Exception
   *
   * @Then the page analytics json should not contain the parameter :parameter
   */
  public function analyticsJsonNotContainsParameter(string $parameter): void {
    $scripts = $this->getSession()->getPage()->findAll("css", "script[type=\"application/json\"]");
    $json_found = FALSE;
    /** @var \Behat\Mink\Element\NodeElement $script */
    foreach ($scripts as $script) {
      $json_value = json_decode($script->getText(), TRUE);
      if (isset($json_value['utility']) && $json_value['utility'] == 'piwik') {
        $json_found = TRUE;
        Assert::assertArrayNotHasKey($parameter, $json_value);
      }
    }
    if (!$json_found) {
      throw new \Exception(sprintf('No analytics json found.'));
    }
  }

}

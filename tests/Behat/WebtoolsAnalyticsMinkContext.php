<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;
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
        Assert::assertEquals($value, $json_value[$parameter] ?? '');
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

  /**
   * Change the weight of table row.
   *
   * Attempts to find the weight select box in a table row
   * containing giving text. This is for administrative pages with ability
   * to change the weight.
   *
   * @param string $weight
   *   The new weight value.
   * @param string $rowText
   *   The text to search for in the table row.
   *
   * @When I select :weight weight in the :rowText row
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function assertClickInTableRow(string $weight, string $rowText): void {
    $page = $this->getSession()->getPage();
    if ($weight_element = $this->getTableRow($page, $rowText)->find('css', 'select.weight')) {
      // Click the link and return.
      $weight_element->selectOption($weight);
      return;
    }
    throw new \Exception(sprintf('Found a row containing "%s", but no weight select box on the page %s', $rowText, $this->getSession()->getCurrentUrl()));
  }

  /**
   * Retrieve a table row containing specified text from a given element.
   *
   * @param \Behat\Mink\Element\Element $element
   *   The element.
   * @param string $search
   *   The text to search for in the table row.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The searched element.
   *
   * @throws \Exception
   *
   * @see \Drupal\DrupalExtension\Context\DrupalContext::getTableRow
   */
  public function getTableRow(Element $element, string $search): NodeElement {
    $rows = $element->findAll('css', 'tr');
    if (empty($rows)) {
      throw new \Exception(sprintf('No rows found on the page %s', $this->getSession()->getCurrentUrl()));
    }
    foreach ($rows as $row) {
      if (strpos($row->getText(), $search) !== FALSE) {
        return $row;
      }
    }
    throw new \Exception(sprintf('Failed to find a row containing "%s" on the page %s', $search, $this->getSession()->getCurrentUrl()));
  }

}

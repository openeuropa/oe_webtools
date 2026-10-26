<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_analytics_rules\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Component\Serialization\Json;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_webtools\Traits\ApplicationJsonAssertTrait;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\oe_webtools_analytics\AnalyticsEventInterface;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;

/**
 * Tests for defining site sections using regular expressions.
 *
 * @group oe_webtools_analytics
 * @group batch1
 */
class SectionRulesTest extends BrowserTestBase {

  use ApplicationJsonAssertTrait;
  use CachedDatabaseInstallTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config',
    'system',
    'language',
    'oe_webtools_analytics',
    'oe_webtools_analytics_rules',
    'oe_webtools_globan',
    'oe_webtools_hotjar',
    'oe_webtools_js_mock',
    'oe_webtools_laco_widget',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->cacheDbInstall = TRUE;
    parent::setUp();
  }

  /**
   * Test that sections for different rules are correctly rendered.
   */
  public function testSectionRender(): void {
    // Configure the site to use analytics.
    $config = \Drupal::configFactory()
      ->getEditable(AnalyticsEventInterface::CONFIG_NAME)
      ->set("siteID", "123")
      ->set("sitePath", "ec.europa.eu");
    $config->save();

    $analytic_rules_storage = \Drupal::entityTypeManager()->getStorage('webtools_analytics_rule');

    // Create first rule under the main administration page.
    $analytic_rules_storage
      ->create([
        'id' => 'id1',
        'section' => 'section1',
        'regex' => '/admin/',
      ])
      ->save();

    // Create a second rule under the main configuration page.
    $analytic_rules_storage
      ->create([
        'id' => 'id2',
        'section' => 'section2',
        'regex' => '/\/admin\/config/',
      ])
      ->save();

    // Frontpage doesn't match any rule so it doesn't render a section.
    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"utility":"analytics","siteID":"123","sitePath":["ec.europa.eu"]}');

    // The administration page matches the first rule so it renders section1.
    $this->drupalGet('admin');
    $this->assertBodyContainsApplicationJson('{"utility":"analytics","siteID":"123","sitePath":["ec.europa.eu"],"siteSection":"section1","is403":true}');

    // The configuration page matches both rules but since they have the same
    // weight, the first rule is applied and section1 is rendered.
    $this->drupalGet('admin/config');
    $this->assertBodyContainsApplicationJson('{"utility":"analytics","siteID":"123","sitePath":["ec.europa.eu"],"siteSection":"section1","is403":true}');

    // Change weight of rules.
    /** @var \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface $id2 */
    $id2 = $analytic_rules_storage
      ->load('id1');

    $id2->set('weight', -9);
    $id2->save();

    /** @var \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface $id2 */
    $id2 = $analytic_rules_storage
      ->load('id2');

    $id2->set('weight', -10);
    $id2->save();

    // The configuration page matches both rules but since the second rule
    // is now lighter the second rule is applied and section2 is rendered.
    $this->drupalGet('admin/config');
    $this->assertBodyContainsApplicationJson('{"utility":"analytics","siteID":"123","sitePath":["ec.europa.eu"],"siteSection":"section2","is403":true}');
  }

  /**
   * Tests managing the rules through the UI.
   */
  public function testRulesUi(): void {
    $this->drupalLogin($this->createUser(['administer webtools analytics']));
    $this->setAnalyticsConfig();
    $assert_session = $this->assertSession();

    // Create a rule.
    $this->createRuleFromUi('rule1', 'examplesection', '|^/custompath/?$|');
    $assert_session->addressEquals('/admin/structure/webtools_analytics_rule');
    $assert_session->pageTextContains('examplesection');
    $this->drupalGet('custompath');
    $this->assertAnalyticsSiteSection('examplesection');

    // Delete the rule.
    $this->drupalGet('admin/structure/webtools_analytics_rule');
    $this->getRuleRow('examplesection')->clickLink('Delete');
    $this->getSession()->getPage()->pressButton('Delete');
    $assert_session->addressEquals('/admin/structure/webtools_analytics_rule');
    $assert_session->pageTextContains('Rule examplesection has been deleted');
    $this->drupalGet('custompath');
    $this->assertAnalyticsSiteSection(NULL);

    // Rules apply by priority.
    $this->createRuleFromUi('rule1', 'examplesection1', '|^/custompath|');
    $assert_session->addressEquals('/admin/structure/webtools_analytics_rule');
    $assert_session->pageTextContains('examplesection1');
    $this->drupalGet('custompath');
    $this->assertAnalyticsSiteSection('examplesection1');

    $this->createRuleFromUi('rule2', 'examplesection2', '|^/custompath/subpage|');
    $assert_session->addressEquals('/admin/structure/webtools_analytics_rule');
    $assert_session->pageTextContains('examplesection2');
    // Both rules match, but the first one still applies.
    $this->drupalGet('custompath/subpage');
    $this->assertAnalyticsSiteSection('examplesection1');

    // Re-order the rules to change their priority.
    $this->drupalGet('admin/structure/webtools_analytics_rule');
    $this->getRuleRow('examplesection1')->selectFieldOption('Weight for examplesection1', '-9');
    $this->getRuleRow('examplesection2')->selectFieldOption('Weight for examplesection2', '-10');
    $this->getSession()->getPage()->pressButton('Save');
    $this->drupalGet('custompath/subpage');
    $this->assertAnalyticsSiteSection('examplesection2');
  }

  /**
   * Tests rules matching on multilingual path aliases.
   */
  public function testMultilingualAliases(): void {
    ConfigurableLanguage::createFromLangcode('fr')->save();
    ConfigurableLanguage::createFromLangcode('nl')->save();
    $this->drupalLogin($this->createUser([
      'administer webtools analytics',
      'access administration pages',
    ]));
    $this->setAnalyticsConfig();

    $path_alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
    foreach (['en' => '/news', 'fr' => '/nouvelles', 'nl' => '/nieuws'] as $langcode => $alias) {
      $path_alias_storage->create([
        'path' => '/admin/config',
        'alias' => $alias,
        'langcode' => $langcode,
      ])->save();
    }

    // A rule matching on the alias of the site default language applies to
    // all the translations of the path.
    $this->createRuleFromUi('news_overview', 'news_overview', '|^/news/?$|', TRUE);
    // Visit the paths as they are, otherwise the outbound path processing
    // would replace the system path with its alias.
    foreach (['/admin/config', '/news', '/fr/nouvelles', '/nl/nieuws'] as $path) {
      $this->drupalGet($this->getAbsoluteUrl($path));
      $this->assertAnalyticsSiteSection('news_overview');
    }
    $this->drupalGet('admin');
    $this->assertAnalyticsSiteSection(NULL);

    \Drupal::entityTypeManager()->getStorage('webtools_analytics_rule')->load('news_overview')->delete();

    // A rule not matching on the alias of the site default language only
    // applies to the current path.
    $this->createRuleFromUi('news_overview_english', 'news_overview_english', '|^/news/?$|');
    $this->drupalGet($this->getAbsoluteUrl('/admin/config'));
    $this->assertAnalyticsSiteSection(NULL);
    $this->drupalGet('news');
    $this->assertAnalyticsSiteSection('news_overview_english');

    $this->createRuleFromUi('news_overview_french', 'news_overview_french', '|^/fr/nouvelles/?$|');
    $this->drupalGet('fr/nouvelles');
    $this->assertAnalyticsSiteSection('news_overview_french');
  }

  /**
   * Configures the site to use analytics.
   */
  protected function setAnalyticsConfig(): void {
    \Drupal::configFactory()
      ->getEditable(AnalyticsEventInterface::CONFIG_NAME)
      ->set('siteID', '123')
      ->set('sitePath', 'sitePath')
      ->save();
  }

  /**
   * Creates a rule through the UI.
   *
   * @param string $id
   *   The rule machine name.
   * @param string $section
   *   The section.
   * @param string $regex
   *   The regular expression.
   * @param bool $match_on_site_default_language
   *   Whether to match on the path alias for the site default language.
   */
  protected function createRuleFromUi(string $id, string $section, string $regex, bool $match_on_site_default_language = FALSE): void {
    $this->drupalGet('admin/structure/webtools_analytics_rule/add');
    $page = $this->getSession()->getPage();
    $page->fillField('Machine-readable name', $id);
    $page->fillField('Section', $section);
    $page->fillField('Regular expression', $regex);
    if ($match_on_site_default_language) {
      $page->checkField('Match translated pages on the path alias for the default language.');
    }
    $page->pressButton('Save');
  }

  /**
   * Returns the row of the rules overview table containing the given section.
   *
   * @param string $section
   *   The section.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The table row.
   */
  protected function getRuleRow(string $section): NodeElement {
    return $this->assertSession()->elementExists('xpath', sprintf('//table//tr[td[contains(., "%s")]]', $section));
  }

  /**
   * Asserts the site section in the analytics JSON of the current page.
   *
   * @param string|null $section
   *   The expected section, or NULL if no section is expected.
   */
  protected function assertAnalyticsSiteSection(?string $section): void {
    $analytics = NULL;
    foreach ($this->getSession()->getPage()->findAll('css', 'script[type="application/json"]') as $script) {
      $json = Json::decode($script->getText());
      if (($json['utility'] ?? NULL) === 'analytics') {
        $analytics = $json;
        break;
      }
    }
    $this->assertNotNull($analytics, 'No analytics JSON found on the page.');

    if ($section === NULL) {
      $this->assertArrayNotHasKey('siteSection', $analytics);
      return;
    }
    $this->assertSame($section, $analytics['siteSection'] ?? NULL);
  }

}

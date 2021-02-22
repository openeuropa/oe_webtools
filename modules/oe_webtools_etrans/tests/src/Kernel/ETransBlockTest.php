<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_etrans\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\oe_webtools_etrans\Plugin\Block\ETransBlock;

/**
 * Tests the block that displays the Webtools eTrans link.
 */
class ETransBlockTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'language',
    'locale',
    'oe_webtools_etrans',
  ];

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The service to set the default language.
   *
   * @var \Drupal\Core\Language\LanguageDefault
   */
  protected $languageDefault;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');
    $this->installConfig(['system']);
    $this->languageManager = \Drupal::service('language_manager');
    $this->languageDefault = \Drupal::service('language.default');
  }

  /**
   * Check that the block content is rendered correctly.
   *
   * @param string $render_as
   *   The render_as configuration option for the block.
   * @param string $render_to
   *   The render_to option.
   * @param string $domain
   *   The domain option.
   * @param int $delay
   *   The delay option.
   * @param string $langcode
   *   The language code to set as the active language.
   *
   * @dataProvider blockRenderingTestProvider
   */
  public function testBlockRendering(string $render_as, string $render_to, string $domain, int $delay, string $langcode): void {
    // Set the current language, so that we can test that the current language
    // is correctly excluded from the list of available translation languages.
    $language = ConfigurableLanguage::createFromLangcode($langcode);
    $this->languageManager->reset();
    $this->languageDefault->set($language);

    // Create a test block using the passed in configuration option and render
    // it.
    $config = [
      'id' => 'oe_webtools_etrans',
      'label' => 'eTrans link',
      'provider' => 'oe_webtools_etrans',
      'label_display' => '0',
      'render_as' => $render_as,
      'render_to' => $render_to,
      'domain' => $domain,
      'delay' => $delay,
    ];
    $plugin = $this->container->get('plugin.manager.block')->createInstance('oe_webtools_etrans', $config);

    $render_array = $plugin->build();
    $rendered_html = (string) $this->container->get('renderer')->renderRoot($render_array);

    // Check that the output of the block is correct.
    $render_as_options = array_map(function (string $render_option) use ($render_as) {
      $value = $render_option === $render_as ? 'true' : 'false';
      return "\"$render_option\":$value";
    }, ETransBlock::RENDER_OPTIONS);
    $render_as = implode(',', $render_as_options);
    $render_to = !empty($render_to) ? ",\"renderTo\":\"$render_to\"" : '';
    $expected_html = "<script type=\"application/json\">{\"service\":\"etrans\",\"languages\":{\"exclude\":[\"$langcode\"]},\"renderAs\":{{$render_as}},\"domain\":\"$domain\",\"delay\":$delay$render_to}</script>\n";

    $this->assertEquals($expected_html, $rendered_html);
  }

  /**
   * Data provider for ::testBlockRendering().
   *
   * @return string[][]
   *   An array of test cases, each one an array with the following values:
   *   - The render_as option of the eTrans block.
   *   - The render_to option.
   *   - The domain option.
   *   - The delay option.
   *   - The language to render.
   *
   * @see ::testBlockRendering()
   */
  public function blockRenderingTestProvider(): array {
    return [
      ['button', '', 'gen', 0, 'en'],
      ['icon', 'main-content', 'gen', 100, 'fr'],
      ['link', '', 'spd', 500, 'es'],
    ];
  }

}

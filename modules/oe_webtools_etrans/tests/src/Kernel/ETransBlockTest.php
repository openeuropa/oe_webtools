<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_etrans\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;

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
    $this->languageManager = $this->container->get('language_manager');
    $this->languageDefault = $this->container->get('language.default');
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
   * @param string $include
   *   The include option.
   * @param string $exclude
   *   The exclude option.
   * @param string $langcode
   *   The language code to set as the active language.
   * @param string $expected_html
   *   The expected rendered block content.
   *
   * @dataProvider blockRenderingTestProvider
   */
  public function testBlockRendering(string $render_as, string $render_to, string $domain, int $delay, string $include, string $exclude, string $langcode, string $expected_html): void {
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
      'include' => $include,
      'exclude' => $exclude,
    ];
    $plugin = $this->container->get('plugin.manager.block')->createInstance('oe_webtools_etrans', $config);

    $render_array = $plugin->build();
    $rendered_html = (string) $this->container->get('renderer')->renderRoot($render_array);

    $this->assertEquals($expected_html, trim($rendered_html));
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
   *   - The include option.
   *   - The exclude option.
   *   - The language to render.
   *   - The expected rendered block content.
   *
   * @see ::testBlockRendering()
   */
  public static function blockRenderingTestProvider(): array {
    return [
      [
        'button',
        '',
        'gen',
        0,
        "
          h1.page__title
          #main-content
        ",
        "

        ",
        'en',
        '<script type="application/json">{"service":"etrans","languages":{"exclude":["en"]},"renderAs":{"button":true,"icon":false,"link":false},"domain":"gen","delay":0,"include":"h1.page__title,#main-content"}</script>',
      ],
      [
        'icon',
        'main-content',
        'gen',
        100,
        "",
        "
          div.comment-wrapper
        ",
        'fr',
        '<script type="application/json">{"service":"etrans","languages":{"exclude":["fr"]},"renderAs":{"button":false,"icon":true,"link":false},"domain":"gen","delay":100,"renderTo":"main-content","exclude":"div.comment-wrapper"}</script>',
      ],
      [
        'link',
        '',
        'spd',
        500,
        "
          #content-block div.main > p

          h1,h2,h3
        ",
        "

          aside
          #nav > a.pager

        ",
        'es',
        '<script type="application/json">{"service":"etrans","languages":{"exclude":["es"]},"renderAs":{"button":false,"icon":false,"link":true},"domain":"spd","delay":500,"include":"#content-block div.main \u003E p,h1,h2,h3","exclude":"aside,#nav \u003E a.pager"}</script>',
      ],
      [
        'button',
        '',
        'gen',
        0,
        '',
        '',
        'pt-pt',
        '<script type="application/json">{"service":"etrans","languages":{"exclude":["pt"]},"renderAs":{"button":true,"icon":false,"link":false},"domain":"gen","delay":0}</script>',
      ],
    ];
  }

}

<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_etrans\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests the unified translations via webtool.
 */
class UnifiedEtransBlockTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'node',
    'oe_multilingual',
    'oe_webtools_etrans',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalCreateContentType([
      'name' => 'Page',
      'type' => 'page',
    ]);

    $this->drupalPlaceBlock('oe_webtools_etrans_unified', ['region' => 'highlighted']);
  }

  /**
   * Tests the unified eTranslation block.
   */
  public function testUnifiedEtransBlockDisplay(): void {
    // Navigate to system 404.
    $this->drupalGet('/random-page');
    $this->assertSession()->addressEquals('/en/random-page');
    // Default language, block not present.
    $this->assertSession()->pageTextNotContains("English is available via eTranslation, the European Commission's machine translation service.");
    $this->drupalGet('/random-page', [
      'language' => ConfigurableLanguage::load('fr'),
    ]);
    $this->assertSession()->addressEquals('/fr/random-page');
    // Non-default language, block present.
    $this->assertSession()->pageTextContains("French is available via eTranslation, the European Commission's machine translation service.");
    $this->assertSession()->pageTextContains("Translate to French");
    $this->assertSession()->pageTextContains("Important information about machine translation");

    // Create a node with english and french translations.
    $node = $this->drupalCreateNode([
      'title' => 'English translation',
      'body' => [
        'value' => "I'm a text that will be translated.",
        'format' => filter_default_format(),
      ],
    ]);
    $translation = $node->addTranslation('fr', [
      'title' => 'Traduction Française',
      'body' => [
        'value' => "Je suis un texte qui va être traduit.",
        'format' => filter_default_format(),
      ],
    ]);
    $translation->save();

    // English translation should not have the block.
    $this->drupalGet($node->toUrl(), [
      'language' => ConfigurableLanguage::load('en'),
    ]);
    $this->assertSession()->pageTextContains("I'm a text that will be translated.");
    $this->assertSession()->pageTextNotContains("English is available via eTranslation, the European Commission's machine translation service.");

    // French translation should not have the block.
    $this->drupalGet($node->toUrl(NULL, [
      'language' => ConfigurableLanguage::load('fr'),
    ]));
    $this->assertSession()->pageTextContains("Je suis un texte qui va être traduit.");
    $this->assertSession()->pageTextNotContains("French is available via eTranslation, the European Commission's machine translation service.");

    // Croatian translation should have the block
    // and display the english text.
    $this->drupalGet($node->toUrl(NULL, [
      'language' => ConfigurableLanguage::load('hr'),
    ]));
    $this->assertSession()->pageTextContains("I'm a text that will be translated.");
    $this->assertSession()->pageTextContains("Croatian is available via eTranslation, the European Commission's machine translation service.");

    $translation = $node->addTranslation('hr', [
      'title' => 'Hrvatski prijevod',
      'body' => [
        'value' => "Ja sam tekst koji će biti preveden.",
        'format' => filter_default_format(),
      ],
    ]);
    $translation->save();
    // Croatian translation should not have the block
    // and display the croatian text.
    $this->drupalGet($node->toUrl(NULL, [
      'language' => ConfigurableLanguage::load('hr'),
    ]));
    $this->assertSession()->pageTextContains("Ja sam tekst koji će biti preveden.");
    $this->assertSession()->pageTextNotContains("Croatian is available via eTranslation, the European Commission's machine translation service.");
  }

  /**
   * Tests that unified eTranslation block is dismissable.
   */
  public function testUnifiedEtransBlockDismissable(): void {
    $this->drupalGet('/random-page', [
      'language' => ConfigurableLanguage::load('fr'),
    ]);
    $this->assertSession()->pageTextContains("French is available via eTranslation, the European Commission's machine translation service.");
    $page = $this->getSession()->getPage();
    $page->find('css', '.utr-close')->click();
    $this->assertSession()->pageTextNotContains("French is available via eTranslation, the European Commission's machine translation service.");
  }

}

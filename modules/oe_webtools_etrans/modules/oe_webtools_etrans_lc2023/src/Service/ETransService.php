<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_etrans_lc2023\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;

/**
 * Helper methods to work with eTranslation.
 */
class ETransService {

  use LoggerChannelTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * ETranslationService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Drupal Entity Type Manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, ModuleHandlerInterface $module_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Check if language is european, excluding default lang.
   *
   * @param string|null $language_code
   *   Language code.
   *
   * @return \Drupal\Core\Language\LanguageInterface|null
   *   Returns language or null.
   */
  public function isLanguageEuropean(?string $language_code = NULL): ?LanguageInterface {
    if (!$this->moduleHandler->moduleExists('oe_multilingual')) {
      return NULL;
    }
    if ($language_code == 'pt') {
      $language_code = 'pt-pt';
    }
    // Load language by ID or fallback to current language.
    $language = $language_code ? $this->languageManager->getLanguage($language_code) : $this->languageManager->getCurrentLanguage();
    if (!$language instanceof LanguageInterface) {
      return NULL;
    }
    $config_manager = $this->entityTypeManager->getStorage('configurable_language');
    $is_eu = $config_manager->load($language->getId())
      ->getThirdPartySetting('oe_multilingual', 'category');
    if ($is_eu === 'eu' && !$language->isDefault()) {
      return $language;
    }
    return NULL;
  }

  /**
   * Get all european languages.
   *
   * @return array
   *   Array with all languages marked as european.
   */
  public function getEuropeanLanguages(): ?array {
    if (!$this->moduleHandler->moduleExists('oe_multilingual')) {
      return NULL;
    }
    $config_manager = $this->entityTypeManager->getStorage('configurable_language');
    // European languages.
    $european_languages = [];
    $languages = $this->languageManager->getLanguages();
    foreach ($languages as $language_code => $language) {
      $etrans_lang = mb_substr($language_code, 0, 2);
      $is_eu = $config_manager->load($language->getId())
        ->getThirdPartySetting('oe_multilingual', 'category');
      if ($is_eu === 'eu' && !$language->isDefault()) {
        $european_languages[$etrans_lang] = $language;
      }
    }
    return $european_languages;
  }

}

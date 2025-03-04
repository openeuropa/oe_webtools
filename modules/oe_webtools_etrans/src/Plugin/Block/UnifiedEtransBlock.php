<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_etrans\Plugin\Block;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Html;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block that unifies Drupal-Etrans translation of current page.
 */
#[Block(
  id: 'oe_webtools_etrans_unified',
  admin_label: new TranslatableMarkup('OpenEuropa Unified eTrans'),
  category: new TranslatableMarkup('Webtools')
)]
class UnifiedEtransBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Default values for the configuration options of this block.
   */
  protected const DEFAULT_CONFIGURATION = [
    'delay' => 0,
    'include' => '',
    'exclude' => '',
  ];

  /**
   * Creates an UnifiedEransBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected LanguageManagerInterface $languageManager,
    protected RouteMatchInterface $routeMatch,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    $translation_exists = (bool) $this->getRouteEntityLangcode();
    return AccessResult::allowedIf(!$translation_exists);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), [
      'url.path',
      'languages:' . LanguageInterface::TYPE_INTERFACE,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $placeholder_id = Html::getUniqueId('etrans-widget');
    // On node route, we take the node original language,
    // which is not necessarily the default language of the website.
    $current_language = $this->languageManager->getCurrentLanguage();
    $langcode_from = $this->getRouteEntityLangcode(TRUE) ?: $this->languageManager->getDefaultLanguage()->getId();
    $langcode_to = $current_language->getId();

    $langcode_from = $this->mapLangcodeToIso($langcode_from);
    $langcode_to = $this->mapLangcodeToIso($langcode_to);

    $json = $this->preparesWtEtransJson($langcode_from, $placeholder_id);

    // Returns UEC webtool eTrans widget.
    $build['etrans_uec'] = [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => Json::encode($json),
      '#attributes' => [
        'type' => 'application/json',
      ],
      '#attached' => [
        'library' => [
          'oe_webtools_etrans/wt_etrans',
        ],
        'drupalSettings' => [
          'path' => [
            'languageTo' => $langcode_to,
          ],
        ],
      ],
    ];

    $build['widget_placholder'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => $placeholder_id,
      ],
    ];

    $message = $this->t("@language is available via eTranslation, the European Commission's machine translation service.", [
      '@language' => $current_language->getName(),
    ], [
      'langcode' => $current_language->getId(),
    ]);
    $translate_link = [
      '#type' => 'link',
      '#url' => Url::fromRoute('<none>', [], ['attributes' => ['class' => ['oe-webtools-unified-etrans--translate']]]),
      '#title' => $this->t('Translate to @language', [
        '@language' => $current_language->getName(),
      ], [
        'langcode' => $current_language->getId(),
      ]),
    ];
    $disclaimer_link = [
      '#type' => 'link',
      '#url' => Url::fromUri('https://commission.europa.eu/languages-our-websites/use-machine-translation-europa_' . $current_language->getId(), [
        'attributes' => [
          'class' => ['webtools-etrans--disclaimer'],
          'target' => '_blank',
        ],
      ]),
      '#title' => $this->t('Important information about machine translation', [], ['langcode' => $current_language->getId()]),
    ];
    $build['translation_request'] = [
      '#theme' => 'block__unified_etrans_request',
      '#message' => $message,
      '#translate_link' => $translate_link,
      '#disclaimer_link' => $disclaimer_link,
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return self::DEFAULT_CONFIGURATION + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['delay'] = [
      '#type' => 'number',
      '#title' => $this->t('Delay'),
      '#min' => 0,
      '#description' => $this->t('The time in milliseconds to delay rendering the translation. Use this on dynamic pages if the HTML element that contains the translation is not immediately available.'),
      '#default_value' => $this->configuration['delay'],
    ];

    $form['include'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Include'),
      '#description' => $this->t('A list of CSS selectors indicating the page elements to be translated, one selector per line. If omitted the entire page will be translated.'),
      '#default_value' => (string) $this->configuration['include'],
    ];

    $form['exclude'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Exclude'),
      '#description' => $this->t('A list of CSS selectors indicating page elements to be excluded from the translation even if they are inside an "include" element. One selector per line.'),
      '#default_value' => (string) $this->configuration['exclude'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);

    foreach (array_keys(self::DEFAULT_CONFIGURATION) as $key) {
      $this->configuration[$key] = $form_state->getValue($key);
    }

    $this->configuration['delay'] = (int) $form_state->getValue('delay');
  }

  /**
   * Get route entity langcode (translation or default).
   *
   * @param bool $default
   *   Whether to fallback to node default site language on node route.
   *
   * @return string
   *   Requested langcode, default one or empty string.
   */
  protected function getRouteEntityLangcode(bool $default = FALSE): string {
    $route = $this->routeMatch->getRouteObject();
    $requested_langcode = $this->languageManager->getCurrentLanguage()->getId();
    if ($route->hasRequirement('node')) {
      /** @var \Drupal\node\NodeInterface $entity */
      $entity = $this->routeMatch->getParameter('node');
      // A translation exists for the requested language.
      if ($entity->hasTranslation($requested_langcode)) {
        return $requested_langcode;
      }
      if (!$default) {
        return '';
      }
      foreach ($entity->getTranslationLanguages() as $language) {
        if ($language->isDefault()) {
          return $language->getId();
        }
      }
    }

    // On route without node entity in the requirements,
    // we do not want to display the block if language is default.
    $default_langcode = $this->languageManager->getDefaultLanguage()->getId();
    if ($requested_langcode === $default_langcode) {
      return $requested_langcode;
    }

    // We return empty otherwise, so we can use eTranslation
    // to translate the current page.
    return '';
  }

  /**
   * Prepares the webtool eTrans UEC.
   *
   * @param string $translation_from
   *   The original langcode of the current page.
   * @param string $placeholder_id
   *   The HTML id of webtool target placeholder.
   *
   * @return array
   *   An UEC JSON format array.
   */
  protected function preparesWtEtransJson(string $translation_from, string $placeholder_id): array {
    $json = [
      'service' => 'etrans',
      'renderAs' => [
        'icon' => FALSE,
        'link' => FALSE,
        'button' => FALSE,
      ],
      'languages' => [
        'source' => $translation_from,
      ],
      'config' => [
        'mode' => 'lc2023',
        'targets' => [
          'receiver' => "#$placeholder_id",
        ],
      ],
      'delay' => (int) $this->configuration['delay'],
    ];

    foreach (['include', 'exclude'] as $option) {
      if (!empty($this->configuration[$option])) {
        $selectors = [];
        foreach (explode("\n", $this->configuration[$option]) as $selector) {
          if ($selector = trim($selector)) {
            $selectors[] = $selector;
          }
        }
        if (!empty($selectors)) {
          $json[$option] = implode(',', $selectors);
        }
      }
    }

    return $json;
  }

  /**
   * Attempt to map langcode to ISO 639-2.
   *
   * @param string $langcode
   *   The drupal langcode.
   *
   * @return string
   *   The ISO 639-2 langcode format.
   */
  protected function mapLangcodeToIso(string $langcode): string {
    // In Drupal, langcode are often in ISO 639-2 format by default.
    // But, some languages use browser formats like 'pt-pt' or 'ta-lk'.
    // A mapping exists in the language module.
    $mapping_file = file_get_contents(DRUPAL_ROOT . '/core/modules/language/config/install/language.mappings.yml');
    $mapping = Yaml::decode($mapping_file ?: '');
    $mapping = array_flip($mapping['map'] ?? []);
    if (isset($mapping[$langcode])) {
      return substr($mapping[$langcode], 0, 2);
    }
    // In some cases, users may add languages that are not standard.
    // Due to this variability, the format of langcode is unpredictable.
    // Therefore, we use only the first two characters as a best guess.
    return substr($langcode, 0, 2);
  }

}

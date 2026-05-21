<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_etrans_lc2023\Plugin\Block;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\oe_webtools_etrans_lc2023\Service\ETransService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a block that displays the Webtools eTrans link - LC 2023.
 *
 * @Block(
 *   id = "oe_webtools_etrans_lc2023",
 *   admin_label = @Translation("OpenEuropa Webtools eTrans - Language Concept 2023"),
 *   category = @Translation("Webtools")
 * )
 */
class ETransBlockLc2023 extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Default values for the configuration options of this block.
   */
  protected const DEFAULT_CONFIGURATION = [
    'receiver' => '',
    'domain' => 'gen',
    'delay' => 0,
    'source' => '',
    'include' => '',
    'exclude' => '',
    'live' => FALSE,
  ];

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The request service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $request;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * E-translation service.
   *
   * @var \Drupal\oe_webtools_etrans_lc2023\Service\ETransService
   */
  protected ETransService $etransService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->languageManager = $container->get('language_manager');
    $instance->request = $container->get('request_stack');
    $instance->configFactory = $container->get('config.factory');
    $instance->etransService = $container->get('oe_webtools_etrans_lc2023.etrans_service');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    // Display only for default language.
    if (!$this->languageManager->getCurrentLanguage()->isDefault()) {
      return [];
    }
    $json = $this->generateEtransJson();

    $build['content'] = [
      '#theme' => 'oe_webtools_etrans_lc2023',
      '#attached' => [
        'library' => [
          'oe_webtools/drupal.webtools-smartloader',
          'oe_webtools_etrans_lc2023/oe_wt_etrans_lc2023',
        ],
      ],
      '#oe_wt_etrans_script' => [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#attributes' => [
          'type' => 'application/json',
        ],
        '#value' => $json,
      ],
    ];
    $cache_contexts = [
      'url.query_args:prefLang',
      'languages:' . LanguageInterface::TYPE_INTERFACE,
    ];
    if ($this->configuration['live']) {
      $cache_contexts[] = 'cookies:etranslive';
    }
    // Set cache context.
    $build['content']['#cache']['contexts'] = $cache_contexts;

    $pref_lang = $this->request->getCurrentRequest()->query->get('prefLang');
    if (!$pref_lang) {
      return $build;
    }
    // Do not continue for non-eu language.
    $eu_language = $this->etransService->isLanguageEuropean($pref_lang);
    if (!$eu_language) {
      return $build;
    }
    // Set additional variables.
    $build['content']['#oe_wt_etrans_lc2023'] = [
      'language' => $eu_language->getName(),
      'language_id' => $eu_language->getId(),
    ];
    $build['content']['#attached']['drupalSettings'] = [
      'oe_wt_etrans' => [
        'preferred_language' => $pref_lang,
        'default_language' => $this->languageManager->getDefaultLanguage()
          ->getId(),
      ],
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
    // eTrans - Language Concept 2023 url.
    $options = ['attributes' => ['target' => '_blank']];
    $url = Link::fromTextAndUrl(
      $this->t('More info'),
      Url::fromUri('https://webtools.europa.eu/showcase-demo/resources/etrans/demo/links/demo/lc2023/lc2023.html',
        $options))->toString();
    // Render as.
    $form['info'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Language Concept 2023. @more_link', ['@more_link' => $url]),
      '#description' => $this->t('Choose how to display the component. If you select Language Concept 2023, you need to specify a "Render to" ID, also known as a Receiver.'),
    ];

    // Render to.
    $form['receiver'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Receiver'),
      '#description' => $this->t('The ID of a HTML element in which the eTrans component will be rendered. Required for Language Concept 2023'),
      '#maxlength' => 64,
      '#default_value' => (string) $this->configuration['receiver'],
      '#required' => TRUE,
    ];

    // Domain.
    $form['domain'] = [
      '#type' => 'radios',
      '#title' => $this->t('Domain'),
      '#options' => [
        'gen' => $this->t('General text'),
        'spd' => $this->t('EU formal language'),
      ],
      '#default_value' => $this->configuration['domain'],
    ];

    // Live.
    $form['live'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Live translation'),
      '#description' => $this->t('When live is set to true, after an user is translating a page to a specific language, all pages visited by user will be automatically translated to the selected language until the user is canceling the translation process.'),
      '#default_value' => $this->configuration['live'] ?? FALSE,
    ];

    // Delay.
    $form['delay'] = [
      '#type' => 'number',
      '#title' => $this->t('Delay'),
      '#min' => 0,
      '#description' => $this->t('The time in milliseconds to delay rendering the translation. Use this on dynamic pages if the HTML element that contains the translation is not immediately available.'),
      '#default_value' => $this->configuration['delay'],
    ];

    // Include.
    $form['include'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Include'),
      '#description' => $this->t('A list of CSS selectors indicating the page elements to be translated, one selector per line. If omitted the entire page will be translated.'),
      '#default_value' => (string) $this->configuration['include'],
    ];

    // Exclude.
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
  public function blockValidate($form, FormStateInterface $form_state): void {
    $receiver_value = $form_state->getValue('receiver');
    if (!empty($receiver_value) && $receiver_value !== Html::cleanCssIdentifier($receiver_value)) {
      $form_state->setErrorByName('receiver', $this->t('Please provide a valid HTML ID.'));
    }
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
   * Generate eTrans json based on block configuration.
   *
   * @return false|string
   *   JSON string.
   */
  private function generateEtransJson(): bool|string {
    // Only pass the first two characters of the language code. The eTrans
    // documentation is not clear on the standard used for language codes
    // but all examples are showing two letters which seems to indicate
    // ISO 639-1:2002. Drupal uses the IETF BCP 47 standard which uses more
    // characters. For example this will convert 'pt-pt' to 'pt'.
    $current_language_id = mb_substr($this->languageManager->getCurrentLanguage()
      ->getId(), 0, 2);
    $json = [
      'service' => 'etrans',
      'languages' => [
        'exclude' => [
          $current_language_id,
        ],
        'source' => $this->languageManager->getDefaultLanguage()->getId(),
      ],
      'domain' => $this->configuration['domain'],
      'delay' => (int) $this->configuration['delay'],
      'config' => [
        'live' => (bool) $this->configuration['live'],
        'mode' => 'lc2023',
        'targets' => [
          'receiver' => Html::cleanCssIdentifier($this->configuration['receiver']),
        ],
      ],
    ];

    foreach (['include', 'exclude'] as $option) {
      if (!empty($this->configuration[$option])) {
        $json[$option] = $this->formatSelectors($option);
      }
    }
    return Json::encode($json);
  }

  /**
   * Format include/exclude selectors.
   *
   * @param string $option
   *   Option group.
   *
   * @return string|null
   *   Returns string or null.
   */
  private function formatSelectors(string $option): ?string {
    $selectors = [];
    foreach (explode("\n", $this->configuration[$option]) as $selector) {
      if ($selector = trim($selector)) {
        $selectors[] = $selector;
      }
    }
    return !empty($selectors) ? implode(',', $selectors) : NULL;
  }

}

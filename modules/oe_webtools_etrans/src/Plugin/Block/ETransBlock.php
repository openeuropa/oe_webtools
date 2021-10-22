<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_etrans\Plugin\Block;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block that displays the Webtools eTrans link.
 *
 * @Block(
 *   id = "oe_webtools_etrans",
 *   admin_label = @Translation("OpenEuropa Webtools eTrans"),
 *   category = @Translation("Webtools")
 * )
 */
class ETransBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The various ways the eTrans link can be rendered.
   */
  public const RENDER_OPTIONS = ['button', 'icon', 'link'];

  /**
   * Default values for the configuration options of this block.
   */
  protected const DEFAULT_CONFIGURATION = [
    'render_as' => 'button',
    'render_to' => '',
    'domain' => 'gen',
    'delay' => 0,
    'include' => '',
    'exclude' => '',
  ];

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Creates an ETransBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $render_as_options = array_fill_keys(self::RENDER_OPTIONS, FALSE);
    $render_as_options[$this->configuration['render_as']] = TRUE;
    $json = [
      'service' => 'etrans',
      'languages' => [
        // Only pass the first two characters of the language code. The eTrans
        // documentation is not clear on the standard used for language codes
        // but all examples are showing two letters which seems to indicate
        // ISO 639-1:2002. Drupal uses the IETF BCP 47 standard which uses more
        // characters. For example this will convert 'pt-pt' to 'pt'.
        'exclude' => [substr($this->languageManager->getCurrentLanguage()->getId(), 0, 2)],
      ],
      'renderAs' => $render_as_options,
      'domain' => $this->configuration['domain'],
      'delay' => (int) $this->configuration['delay'],
    ];

    if (!empty($this->configuration['render_to'])) {
      $json['renderTo'] = Html::cleanCssIdentifier($this->configuration['render_to']);
    }

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

    $build = [
      '#cache' => [
        'contexts' => ['languages:' . LanguageInterface::TYPE_INTERFACE],
      ],
    ];
    $build['content'] = [
      '#attached' => ['library' => ['oe_webtools/drupal.webtools-smartloader']],
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => Json::encode($json),
      '#attributes' => ['type' => 'application/json'],
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

    // Render as.
    $form['render_as'] = [
      '#type' => 'radios',
      '#title' => $this->t('Render as'),
      '#options' => [
        'button' => $this->t('Button'),
        'icon' => $this->t('Icon'),
        'link' => $this->t('Link'),
      ],
      '#default_value' => $this->configuration['render_as'],
    ];

    // Render to.
    $form['render_to'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Render to'),
      '#description' => $this->t('The ID of a HTML element in which the eTrans component will be rendered. If omitted the component will be rendered inside the block.'),
      '#maxlength' => 64,
      '#default_value' => (string) $this->configuration['render_to'],
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
    $render_to_value = $form_state->getValue('render_to');
    if (!empty($render_to_value) && $render_to_value !== Html::cleanCssIdentifier($render_to_value)) {
      $form_state->setErrorByName('render_to', $this->t('Please provide a valid HTML ID.'));
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

}

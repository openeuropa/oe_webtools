<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_etrans\Plugin\Block;

use Drupal\Component\Serialization\Json;
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
  protected const RENDER_OPTIONS = ['button', 'icon', 'link'];

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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
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
  public function build() {
    $render_as_options = array_fill_keys(self::RENDER_OPTIONS, FALSE);
    $render_as_options[$this->configuration['render_as']] = TRUE;
    $json = [
      'service' => 'etrans',
      'languages' => [
        'exclude' => [$this->languageManager->getCurrentLanguage()->getId()],
      ],
      'renderAs' => $render_as_options,
    ];
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
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
        'render_as' => 'button',
      ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
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
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['render_as'] = $form_state->getValue('render_as');
  }

}

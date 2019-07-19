<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_globan\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The form for managing the configuration of the Webtools Globan module.
 */
class WebtoolsGlobanSettingsForm extends ConfigFormBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a WebtoolsGlobanSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The Language Manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager) {
    parent::__construct($config_factory);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'oe_webtools_globan_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('oe_webtools_globan.settings');

    $form['globan_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Global banner settings'),
    ];
    $form['globan_settings']['livepreview'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Check appearance'),
      '#markup' => '<div id="globan-preview"></div>',
      '#description' => $this->t('Link for testing appearance of global banner.'),
    ];
    $form['globan_settings']['display_eu_flag'] = [
      '#type' => 'select',
      '#title' => $this->t('Display EU flag'),
      '#options' => [
        '0' => $this->t('No - hide flag'),
        '1' => $this->t('Yes - display flag'),
      ],
      '#description' => $this->t('Enable or disable the EU flag icon in the Global Banner.'),
      '#default_value' => $config->get('display_eu_flag') ?? '1',
    ];
    $form['globan_settings']['background_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Background theme'),
      '#options' => [
        '0' => $this->t('Light'),
        '1' => $this->t('Dark'),
      ],
      '#description' => $this->t("Select whether the Global Banner theme is light or dark, in order to correspond to your site's design."),
      '#default_value' => $config->get('background_theme') ?? '1',
    ];
    $form['globan_settings']['eu_institutions_links'] = [
      '#type' => 'select',
      '#title' => $this->t('See all EU Institutions and bodies'),
      '#options' => [
        '1' => $this->t('Yes - show link'),
        '0' => $this->t('No - hide link'),
      ],
      '#description' => $this->t('Show or hide a link to all EU institutions and Bodies, which can assist visitors with navigation.'),
      '#default_value' => $config->get('eu_institutions_links') ?? '1',
    ];
    $lang_options = [];
    foreach ($this->languageManager->getLanguages() as $language) {
      $lang_options[$language->getId()] = $language->getName();
    }
    $form['globan_settings']['override_page_lang'] = [
      '#type' => 'select',
      '#title' => $this->t('Override page language'),
      '#options' => $lang_options,
      '#empty_value' => NULL,
      '#empty_option' => $this->t('No, use language of current page'),
      '#description' => $this->t('ONLY use this option if you want to display a language, different from current page! The Global Banner displays in the language of the current page. It supports all 24 EU languages, as well as these non-EU languages.'),
      '#default_value' => $config->get('override_page_lang') ?? NULL,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('oe_webtools_globan.settings')
      ->set('display_eu_flag', $form_state->getValue('display_eu_flag'))
      ->set('background_theme', $form_state->getValue('background_theme'))
      ->set('eu_institutions_links', $form_state->getValue('eu_institutions_links'))
      ->set('override_page_lang', $form_state->getValue('override_page_lang'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['oe_webtools_globan.settings'];
  }

}

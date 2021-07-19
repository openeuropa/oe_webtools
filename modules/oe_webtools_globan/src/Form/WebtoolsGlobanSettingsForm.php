<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_globan\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configures the Webtools Global banner.
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
    $form['globan_settings']['display_eu_flag'] = [
      '#type' => 'select',
      '#title' => $this->t('Display the EU flag'),
      '#options' => [
        0 => $this->t('No'),
        1 => $this->t('Yes'),
      ],
      '#description' => $this->t('Hide or show the EU flag icon in the Global Banner.'),
      '#default_value' => empty($config->get('display_eu_flag')) ? 0 : 1,
    ];
    $form['globan_settings']['background_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Background theme'),
      '#options' => [
        'light' => $this->t('Light'),
        'dark' => $this->t('Dark'),
      ],
      '#description' => $this->t('Whether to show the banner in light or black background.'),
      '#default_value' => $config->get('background_theme') ?? 'dark',
    ];
    $form['globan_settings']['display_eu_institutions_links'] = [
      '#type' => 'select',
      '#title' => $this->t('Link to all EU Institutions and bodies'),
      '#options' => [
        0 => $this->t('No'),
        1 => $this->t('Yes'),
      ],
      '#description' => $this->t('Hide or show a link to all EU institutions and bodies.'),
      '#default_value' => empty($config->get('display_eu_institutions_links')) ? 0 : 1,
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
      '#description' => $this->t('ONLY use this option if you want to display a language, different from current page! The global banner displays in the language of the current page. It supports all 24 EU languages, as well as these non-EU languages.'),
      '#default_value' => $config->get('override_page_lang') ?? NULL,
    ];

    $form['globan_settings']['sticky'] = [
      '#type' => 'select',
      '#title' => $this->t('Sticky'),
      '#options' => [
        0 => $this->t('No'),
        1 => $this->t('Yes'),
      ],
      '#description' => $this->t('Whether to make the banner sticky on top of the page or not.'),
      '#default_value' => $config->get('sticky') ? 1 : 0,
    ];

    $form['globan_settings']['zindex'] = [
      '#type' => 'number',
      '#title' => $this->t('Z-index'),
      '#description' => $this->t('Adapt the banner z-index value depending on your layout design. Default is 40.'),
      '#default_value' => $config->get('zindex'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('oe_webtools_globan.settings')
      ->set('display_eu_flag', (bool) $form_state->getValue('display_eu_flag'))
      ->set('background_theme', $form_state->getValue('background_theme'))
      ->set('display_eu_institutions_links', (bool) $form_state->getValue('display_eu_institutions_links'))
      ->set('override_page_lang', $form_state->getValue('override_page_lang'))
      ->set('sticky', (bool) $form_state->getValue('sticky'))
      ->set('zindex', $form_state->getValue('zindex'))
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

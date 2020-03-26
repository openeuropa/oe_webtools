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

    $form['display_eu_flag'] = [
      '#type' => 'select',
      '#title' => $this->t('Display the EU flag'),
      '#options' => [
        0 => $this->t('No'),
        1 => $this->t('Yes'),
      ],
      '#description' => $this->t('Hide or show the EU flag icon in the Global Banner.'),
      '#default_value' => empty($config->get('display_eu_flag')) ? 0 : 1,
    ];
    $form['background_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Background theme'),
      '#options' => [
        'light' => $this->t('Light'),
        'dark' => $this->t('Dark'),
      ],
      '#description' => $this->t('Whether to show the banner in light or black background.'),
      '#default_value' => $config->get('background_theme') ?? 'dark',
    ];
    $form['display_eu_institutions_links'] = [
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
    $form['override_page_lang'] = [
      '#type' => 'select',
      '#title' => $this->t('Override page language'),
      '#options' => $lang_options,
      '#empty_value' => NULL,
      '#empty_option' => $this->t('No, use language of current page'),
      '#description' => $this->t('ONLY use this option if you want to display a language, different from current page! The global banner displays in the language of the current page. It supports all 24 EU languages, as well as these non-EU languages.'),
      '#default_value' => $config->get('override_page_lang') ?? NULL,
    ];
    $form['visibility'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('Banner visibility'),
    ];
    $form['visibility']['action'] = [
      '#type' => 'radios',
      '#options' => [
        'show' => $this->t('Show for the listed pages'),
        'hide' => $this->t('Hide for the listed pages'),
      ],
      '#default_value' => $config->get('visibility.action'),
    ];
    $form['visibility']['pages'] = [
      '#type' => 'textarea',
      '#description' => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. An example path is /user/* for every user page. <front> is the front page. Note that the banner cannot be displayed on administrative pages regardless of this configuration."),
      '#default_value' => $config->get('visibility.pages'),
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
      ->set('visibility', $form_state->getValue('visibility'))
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

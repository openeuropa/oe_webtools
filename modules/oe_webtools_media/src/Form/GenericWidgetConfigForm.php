<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_media\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Config form for the generic widget.
 */
class GenericWidgetConfigForm extends ConfigFormBase {

  /**
   * Name of the config being edited.
   */
  const CONFIGNAME = 'oe_webtools_media.generic_widget_settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oe_webtools_media_generic_widget_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $blacklist = '';
    if (!empty($this->config(static::CONFIGNAME)->get('blacklist'))) {
      $blacklist = implode(PHP_EOL, $this->config(static::CONFIGNAME)->get('blacklist'));
    }
    $form['blacklist'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Blacklist'),
      '#description' => $this->t('The Webtools services that are blacklisted in the generic widget, one per line.'),
      '#default_value' => $blacklist,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $blacklist = $form_state->getValue('blacklist') ?? '';
    $blacklist = array_filter(preg_split("/\r\n/", $blacklist));

    $this->config(static::CONFIGNAME)
      ->set('blacklist', $blacklist)
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['oe_webtools_media.generic_widget_settings'];
  }

}

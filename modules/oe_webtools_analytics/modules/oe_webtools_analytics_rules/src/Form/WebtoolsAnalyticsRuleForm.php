<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Builds the form to create and edit Webtools Analytics rule entities.
 */
class WebtoolsAnalyticsRuleForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface $rule */
    $rule = $this->entity;

    $form['section'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Section'),
      '#maxlength' => 255,
      '#default_value' => $rule->getSection(),
      '#description' => $this->t('The section activated by the rule.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $rule->id(),
      '#machine_name' => [
        'exists' => '\Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRule::load',
        'source' => ['section'],
      ],
      '#disabled' => !$rule->isNew(),
    ];

    $form['match_on_site_default_language'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Match translated pages on the path alias for the default language.'),
      '#default_value' => $rule->matchOnSiteDefaultLanguage(),
      '#description' => $this->t('If checked, the matching will be done on the path alias for the translation in the default language.<br>For example, if your default language is English and your news articles have paths that start with <code>/news/</code> then you can enable this option and provide <code>|^/news/.+|</code> as the regular expression. The rule would then also be applied to the translated news articles with paths <code>/fr/nouvelles/*</code> and <code>/nl/nieuws/*</code>.'),
    ];

    $form['regex'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Regular expression'),
      '#maxlength' => 255,
      '#default_value' => $rule->getRegex(),
      '#description' => $this->t('The regular expression to be used to match the page URI. E.g.: <code>|^/article/.+|</code>'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if (@preg_match($form_state->getValue('regex'), 'openeuropa') === FALSE) {
      $errors = [
        PREG_NO_ERROR => 'Code 0 : No errors',
        PREG_INTERNAL_ERROR => 'Code 1 : There was an internal PCRE error',
        PREG_BACKTRACK_LIMIT_ERROR => 'Code 2 : Backtrack limit was exhausted',
        PREG_RECURSION_LIMIT_ERROR => 'Code 3 : Recursion limit was exhausted',
        PREG_BAD_UTF8_ERROR => 'Code 4 : The offset didn\'t correspond to the begin of a valid UTF-8 code point',
        PREG_BAD_UTF8_OFFSET_ERROR => 'Code 5 : Malformed UTF-8 data',
      ];

      $form_state->setErrorByName('regex', $this->t('The regex is not valid. (%error)', ['%error' => $errors[preg_last_error()]]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): void {
    /** @var \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface $rule */
    $rule = $this->entity;
    $status = $rule->save();

    switch ($status) {
      case SAVED_NEW:
        $message = $this->t('Created the %label Webtools Analytics rule.', [
          '%label' => $rule->label(),
        ]);
        break;

      default:
        $message = $this->t('Saved the %label Webtools Analytics rule.', [
          '%label' => $rule->label(),
        ]);
    }

    $this->messenger()->addMessage($message);
    $form_state->setRedirectUrl($rule->toUrl('collection'));
  }

}

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
      '#description' => $this->t("The section activated by the rule."),
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

    $form['regex'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Regex'),
      '#maxlength' => 255,
      '#default_value' => $rule->getRegex(),
      '#description' => $this->t("The regular expression to be used to match the site's paths. E.g.: /example/.*/"),
      '#required' => TRUE,
    ];

    return $form;
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

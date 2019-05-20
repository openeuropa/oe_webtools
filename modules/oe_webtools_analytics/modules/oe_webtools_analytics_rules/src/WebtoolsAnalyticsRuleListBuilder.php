<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface;

/**
 * Listing of Webtools Analytics section rules.
 */
class WebtoolsAnalyticsRuleListBuilder extends DraggableListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oe_webtools_analytics_rules_list_builder_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['section'] = $this->t('Section');
    $header['regexp'] = $this->t('Regular expression');
    $header['match_on_site_default_language'] = $this->t('Match on path alias for site default language');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    if (!$entity instanceof WebtoolsAnalyticsRuleInterface) {
      throw new \InvalidArgumentException('Only Webtools Analytics rules can be listed.');
    }
    $header['section'] = $row['label'] = $entity->getSection();
    $row['regexp'] = [
      '#markup' => $entity->getRegex(),
    ];
    $row['match_on_site_default_language'] = [
      '#markup' => $entity->matchOnSiteDefaultLanguage() ? $this->t('Yes') : $this->t('No'),
    ];

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['#prefix'] = '<p>' . t('This page provides a drag-and-drop interface for managing matching priority of <em>webtools analytics rules</em> for each page.') . '</p>';
    return $form;
  }

}

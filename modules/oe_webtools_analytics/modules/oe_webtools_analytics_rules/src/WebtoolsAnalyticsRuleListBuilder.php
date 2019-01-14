<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Listing of Webtools Analytics section rules.
 */
class WebtoolsAnalyticsRuleListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['section'] = $this->t('Section');
    $header['match_on_site_default_language'] = $this->t('Match on path alias for site default language');
    $header['regex'] = $this->t('Regex');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    $row['section'] = $entity->getSection();
    $row['match_on_site_default_language'] = $entity->matchOnSiteDefaultLanguage() ? $this->t('Yes') : $this->t('No');
    $row['id'] = $entity->getRegex();

    return $row + parent::buildRow($entity);
  }

}

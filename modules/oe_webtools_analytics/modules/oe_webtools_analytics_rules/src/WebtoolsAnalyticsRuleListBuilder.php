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
    $header['multilingual'] = $this->t('Multilingual');
    $header['regex'] = $this->t('Regex');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    $row['section'] = $entity->getSection();
    $row['multilingual'] = $entity->isSupportMultilingualAliases() ? $this->t('Yes') : $this->t('No');
    $row['id'] = $entity->getRegex();

    return $row + parent::buildRow($entity);
  }

}

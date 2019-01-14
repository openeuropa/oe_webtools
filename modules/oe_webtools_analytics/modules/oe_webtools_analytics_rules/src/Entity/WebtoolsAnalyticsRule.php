<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Webtools Analytics rule entity.
 *
 * @ConfigEntityType(
 *   id = "webtools_analytics_rule",
 *   label = @Translation("Webtools Analytics site section rule"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\oe_webtools_analytics_rules\WebtoolsAnalyticsRuleListBuilder",
 *     "form" = {
 *       "add" = "Drupal\oe_webtools_analytics_rules\Form\WebtoolsAnalyticsRuleForm",
 *       "edit" = "Drupal\oe_webtools_analytics_rules\Form\WebtoolsAnalyticsRuleForm",
 *       "delete" = "Drupal\oe_webtools_analytics_rules\Form\WebtoolsAnalyticsRuleDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "webtools_analytics_rule",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "section" = "section",
 *     "match_on_site_default_language" = "match_on_site_default_language",
 *     "regex" = "regex",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/webtools_analytics_rule/{webtools_analytics_rule}",
 *     "add-form" = "/admin/structure/webtools_analytics_rule/add",
 *     "edit-form" = "/admin/structure/webtools_analytics_rule/{webtools_analytics_rule}/edit",
 *     "delete-form" = "/admin/structure/webtools_analytics_rule/{webtools_analytics_rule}/delete",
 *     "collection" = "/admin/structure/webtools_analytics_rule"
 *   }
 * )
 */
class WebtoolsAnalyticsRule extends ConfigEntityBase implements WebtoolsAnalyticsRuleInterface {

  /**
   * The entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Webtools Analytics site section.
   *
   * @var string
   */
  protected $section = '';

  /**
   * The Webtools Analytics rule applicable for multilingual aliases.
   *
   * @var bool
   */
  protected $match_on_site_default_language = FALSE;

  /**
   * The regular expression to be applied.
   *
   * @var string
   */
  protected $regex = '';

  /**
   * {@inheritdoc}
   */
  public function getSection(): string {
    return $this->section;
  }

  /**
   * {@inheritdoc}
   */
  public function getRegex(): string {
    return $this->regex;
  }

  /**
   * {@inheritdoc}
   */
  public function matchOnSiteDefaultLanguage(): bool {
    return (bool) $this->match_on_site_default_language;
  }

}

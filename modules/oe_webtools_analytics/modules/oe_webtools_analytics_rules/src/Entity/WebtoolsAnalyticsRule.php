<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Webtools Analytics rule entity.
 *
 * @ConfigEntityType(
 *   id = "webtools_analytics_rule",
 *   label = @Translation("Webtools Analytics rule"),
 *   label_collection = @Translation("Webtools Analytics rules"),
 *   label_singular = @Translation("Webtools Analytics rule"),
 *   label_plural = @Translation("Webtools Analytics rules"),
 *   label_count = @PluralTranslation(
 *     singular = "@count Webtools Analytics rule",
 *     plural = "@count Webtools Analytics rules",
 *   ),
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
 *   admin_permission = "administer webtools analytics",
 *   entity_keys = {
 *     "id" = "id",
 *     "weight" = "weight"
 *   },
 *   config_export = {
 *     "id",
 *     "section",
 *     "match_on_site_default_language",
 *     "regex",
 *     "weight",
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
   * Indicates if the rule should be applied on the default site language alias.
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

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    // Add a dependency on the site configuration if we rely on the site default
    // language.
    if ($this->matchOnSiteDefaultLanguage()) {
      $this->addDependency('config', 'system.site');
    }

    return $this;
  }

}

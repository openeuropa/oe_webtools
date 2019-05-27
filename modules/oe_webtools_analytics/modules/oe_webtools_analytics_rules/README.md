Webtools Analytics Rules
========================

This module allows an analytics engineer to create rules that can be used to
match page URIs to site sections using regular expressions.


Usage
-----

1. Enable the module.
1. Create a user role "Analytics engineer" and grant it the
   "Administer Webtools Analytics" permission.
1. Log in as a user with the "Analytics engineer" role.
1. Manage rules at Administration > Structure > Webtools Analytics rules.


Creating rules
--------------

A rule consists of the following data:

### Site section

The site section that will be included in the analytics request. For more
details see [Site
sections](https://webgate.ec.europa.eu/CITnet/confluence/display/NEXTEUROPA/Adapting+the+Next+Europa+Piwik+Module+%3A+add+sections).

### Regular expression

The regular expression that is used to match the page URI. These are standard
PCRE expressions. For example to match all pages that have a URI that starts
with `/news/` you can use the regular expression `|^/news/|`.

### Match translated pages on the path alias for the default language

An option to perform the matching on the path alias for the translation in the
default language of the website. This is intended to facilitate the creation of
rules for websites that have a policy to publish all content in the default
language, and translate the content into different languages.

For example, if your default language is English and your news articles have
paths that start with `/news/` then you can enable this option and provide
`|^/news/.+|` as the regular expression. The rule would then also be applied to
the translated news articles with paths `/fr/nouvelles/` and `/nl/nieuws/`.


Known issues and limitations
----------------------------

* By design this module relies only on the URI (a.k.a. "path") of a page to
  determine the site section. Since the URI is dynamic and does not follow
  strict rules this cannot be 100% relied upon; a content editor may decide to
  create a unique path for a page, multiple aliases might exist for the same
  page, etc.

  However from the perspective of an analytics engineer / SEO expert it is a
  common practice to rely upon the URI since they do not have intimate knowledge
  of the site structure and a hierarchical URI is the best possible way they
  have to determine the content structure. To ensure good results it is highly
  recommended to use modules such as
  [Pathauto](https://www.drupal.org/project/pathauto) to provide structured URIs
  for site content.

  Websites that have more complex requirements are suggested to not use this
  module but implement a customized subscriber for the `AnalyticsEvent` provided
  by the Webtools Analytics module.
* The rules currently have no priority. The rules are checked in the order which
  they are returned by the database and the first match that is found will
  determine the site section. This may cause problems if a URI matches multiple
  rules.

  Ref.
  [OPENEUROPA-1633](https://webgate.ec.europa.eu/CITnet/jira/browse/OPENEUROPA-1633)
* The option to match multilingual content on the path alias for the default
  language only works if a translation exists in the default content of the
  website.

  Ref.
  [OPENEUROPA-1637](https://webgate.ec.europa.eu/CITnet/jira/browse/OPENEUROPA-1637)
* There currently is no support for creating rules based on system paths. This
  means that paths such as `/node/*`, `/user` or `/admin/config/*` that have an
  alias in the default language will not receive the correct section.

  Ref.
  [OPENEUROPA-1636](https://webgate.ec.europa.eu/CITnet/jira/browse/OPENEUROPA-1636)
* There currently is no dedicated permission to manage rules.

  Ref.
  [OPENEUROPA-1638](https://webgate.ec.europa.eu/CITnet/jira/browse/OPENEUROPA-1638)
* When creating a new rule a validation error appears when a section is being
  reused: "The machine-readable name is already in use. It must be unique.".
  This can be solved by manually changing the machine name to be unique, for
  example by appending `_1` to it.

  Ref.
  [OPENEUROPA-1640](https://webgate.ec.europa.eu/CITnet/jira/browse/OPENEUROPA-1640)

# OpenEuropa Webtools eTrans

This component provides integration with the Webtools eTranslation service.

## What it does
The `oe_webtools_etrans` module offers 2 blocks:
* OpenEuropa Webtools eTrans: This block displays a "Translate this page" link, 
allowing your visitors to select a language from the official EU language list in a modal. 
Upon selection, the page will be translated using the eTrans service.
* OpenEuropa Unified eTrans: This block displays a banner on non-translated pages, 
offering your users the opportunity to translate the page into the language of their choice, 
selected from the interface.


For more information see [here](https://webgate.ec.europa.eu/fpfis/wikis/display/webtools/eTranslation+component).

## How to use

### OpenEuropa Webtools eTrans

To add the "Translate this page" link to your website, simply place the OpenEuropa Webtools eTrans block in the desired location. 
This block is primarily managed by the webtools API. 
The selection of available languages for translation is provided by the webtools service.

### OpenEuropa Unified eTrans

To use the OpenEuropa Unified eTrans block, ensure the following steps are completed:

- Enable at least one language other than English in the Drupal language settings (/admin/config/regional/language).
- Place the language switcher block on your website, allowing users to select their preferred language.
- Add the OpenEuropa Unified eTrans block to the desired location on your website.

Once a user visits a page that does not have a translation available and is browsing the website in a language
other than the default one, they will be presented with a banner offering them the opportunity 
to translate the page using the eTrans service.

## Using path alias for non translated entities

The purpose of the unified eTrans block is to allow users to navigate the website in their preferred language. 
When using [pathauto](https://www.drupal.org/project/pathauto), URL aliases are generated for translated pages. 
However, non-translated pages will still use the default Drupal path (/node/nid) when accessed by users.

Currently, there is a [patch available](https://www.drupal.org/project/pathauto/issues/2946354) that enables 
the use of the default language alias for untranslated content.

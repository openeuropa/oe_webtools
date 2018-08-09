# OpenEuropa Webtools Laco Widget

Provides a widget that integrates with the Webtools Laco service and which checks if a 
link to a page or document is available in other translations.

### How it works

This module simply outputs the Webtools Laco Widget on every page and provides default
configurations for it. More information on this service can be found [here](https://webgate.ec.europa.eu/fpfis/wikis/pages/viewpage.action?spaceKey=webtools&title=Language+Coverage).


### Configuration

The default widget that is being output is the following:

```
{
    "service"    : "laco",
    "include"    : "body",
    "exclude"    : ".nolaco, .more-link, .pager"
    "icon"       : "dot",
    "coverage" : {
        "document" : "any",
        "page" : false
    }
}
```

The most important aspects of this configuration are the `include` and `exclude`
selectors. The former specifies the CSS selectors within which to apply the widget 
(can target links or parents of links). The latter provides the possibility to
exclude certain selectors. You can find more information on the rest of the 
configuration in the [Laco technical documentation]([here](https://webgate.ec.europa.eu/fpfis/wikis/display/webtools/Language+Coverage+-+Technical+details)).

Without any `include` selectors, the widget will not print on the page.

### Overriding configuration

This configuration is stored as a regular Drupal configuration object so you
can override it inside the `settings.php` file. For example:

```
$config['oe_webtools_laco_widget.settings']['include'] = ['.test', '.test2'];
$config['oe_webtools_laco_widget.settings']['exclude'] = ['.test-ex', '.test2-ex'];
$config['oe_webtools_laco_widget.settings']['coverage'] = [
  'document' => 'other',
  'page' => 'false',
];
$config['oe_webtools_laco_widget.settings']['icon'] = 'all';

```

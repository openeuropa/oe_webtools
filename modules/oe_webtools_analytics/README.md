# OpenEuropa Webtools Analytics

A Webtools Laco service that provides information on page access to a 3rd party
analytics service.

### How it works

This module triggers an event on a page load that allows subscribers to decide
if the particular route is going to be tracked or not. It also allows to define
all the available variables to customize the information being send to the
analytics service. You can find more information
[here](http://ec.europa.eu/ipg/services/analytics/).

If the route is valid and needs to be tracked, the module adds a new attachment
to the page with the information needed for the webtools analytics service to
start gathering visitor information.

### How to use

#### Enable the module

```bash
drush en oe_webtools_analytics
```
Once enabled, the module will provide the minimum required parameters for all
your sites to be tracked. If you wish to fine grain which specific routes are
going to be tracked, you will need to subscribe to the AnalyticsEvent event and
provide your custom logic there.

#### Required configuration

In order to be able to track visitors to your site the Webtool Analytics module
needs a default configuration in the form of two variables:

* Site ID: This is the unique ID that identifies your site.
* Site path: This is the base path that leads to your site.
* Instance: PIWIK server instance (default value "ec.europa.eu")

This configuration can be provided in 2 ways:
* Set up the following variables in  your sites settings.php file:

```
$config['oe_webtools_analytics.settings']['siteID'] = '123';
$config['oe_webtools_analytics.settings']['sitePath'] = 'ec.europa.eu/build';
$config['oe_webtools_analytics.settings']['instance'] = 'ec.europa.eu';

```

* Using the settings form located on this path "admin/config/system/oe_webtools_analytics"

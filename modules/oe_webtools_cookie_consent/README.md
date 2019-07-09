# OpenEuropa Webtools Cookie consent
The OpenEuropa Webtools Cookie consent provides integration with the Cookie consent service.

## How to use
Simply install the module and all available options will be enabled. Uninstall the module to disable the services.

## What it does
The `oe_webtools_cookie_consent` module performs 3 different tasks:
* Provides a banner that allows the user whether to accept or refuse tickets from the website.
* Preprocesses the media_oembed iframes and alters the URL to go through the EC cookie consent service.
* Preprocesses iframes provided by the video_embed_field or the video_embed_wysiwyg modules and also redirects
the source through the EC cookie consent service.

# OpenEuropa Webtools eTrans - Language Concept 2023

This Drupal 10 custom module provides a block that adds machine translation
capabilities for the preferred EU language of a user. It is based on the
eTrans - Language Concept 2023 from OpenEuropa Webtools.

More information about this concept can be found
[here](https://webtools.europa.eu/showcase-demo/resources/etrans/demo/links/demo/lc2023/lc2023_live.html).

## Features

When a user attempts to change the language of the page and the selected
language is one of the 24 languages supported by eTranslation, the user
stays on the same page. A banner for the selected language appears where
the user can use machine translation for this page.
The module also supports live translation.

## Dependencies

This module depends on the `oe_multilingual` module.

## How to use

1. Install the module as you would any other Drupal module.
2. Navigate to the block layout page (`/admin/structure/block`)
3. Locate the region where you want to place the block and click the
   "Place block" button.
4. From the list of available blocks, find "OpenEuropa Webtools eTrans -
   Language Concept 2023" and click the "Place block" button next to it.
5. Configure the block settings as needed and click "Save block".

The OpenEuropa Webtools eTrans - Language Concept 2023 block should now
appear on your site in the chosen region and provide machine translation
capabilities for the preferred EU language of a user.

## Configuration

The block has a number of settings that control its behavior:

- **Receiver**: The ID of a HTML element in which the eTrans component
  will be rendered.
- **Domain**: The domain for translation, can be 'General text' or 'EU
  formal language'.
- **Delay**: The time in milliseconds to delay rendering the translation.
- **Live translation**: When enabled, all pages visited by a user will be
  automatically translated to the selected language until the user cancels
  the translation process.
- **Include**: A list of CSS selectors indicating the page elements to be
  translated.
- **Exclude**: A list of CSS selectors indicating page elements to be
  excluded from the translation.

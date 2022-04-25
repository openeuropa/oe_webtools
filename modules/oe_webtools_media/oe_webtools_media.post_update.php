<?php

/**
 * @file
 * Post update functions for OpenEuropa Webtools Media module.
 */

declare(strict_types = 1);

use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\MediaType;

/**
 * Move png files for webtools media types into the public folder.
 *
 * @see \media_install()
 */
function oe_webtools_media_post_update_00001(): void {
  $source = drupal_get_path('module', 'oe_webtools_media') . '/images/icons';
  $destination = \Drupal::config('media.settings')->get('icon_base_uri');
  /** @var \Drupal\Core\File\FileSystemInterface $file_system */
  $file_system = \Drupal::service('file_system');
  $file_system->prepareDirectory($destination, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

  $files = $file_system->scanDirectory($source, '/.*\.(png)$/');
  foreach ($files as $file) {
    if (!file_exists($destination . DIRECTORY_SEPARATOR . $file->filename)) {
      try {
        $file_system->copy($file->uri, $destination, FileSystemInterface::EXISTS_ERROR);
      }
      catch (FileException $e) {
        // Ignore and continue.
      }
    }
  }
}

/**
 * Configure the webtools media blacklist config with existing services.
 */
function oe_webtools_media_post_update_00002(): void {
  /** @var \Drupal\media\MediaTypeInterface[] $media_types */
  $media_types = MediaType::loadMultiple();
  $blacklist = [
    'charts',
    'chart',
    'racing',
    'map',
    'smk',
    'opwidget',
    'etrans',
    'cdown',
  ];
  foreach ($media_types as $media_type) {
    if ($media_type->get('source') !== 'webtools') {
      continue;
    }

    $source_config = $media_type->get('source_configuration');
    if ($source_config['widget_type'] !== 'generic') {
      continue;
    }

    $source_config['generic_widget_type_blacklist'] = $blacklist;
    $media_type->set('source_configuration', $source_config);
    $media_type->save();
  }
}

<?php

// namespace S1SYPHOS\WEBP;

// use c;
// use WebPConvert;

class Settings {

  /**
   * Returns the default options for `kirby-webp`
   *
   * @return array
   */

  public static function __callStatic($name, $args) {

    // Set prefix
    $prefix = 'plugin.kirby-webp.';

    // Set config names and fallbacks as settings
    $settings = [
      // TODO: $kirby->option('thumb.quality') ?
      'actions'             => ['upload'],
      'quality'             => 90, // Desired WebP compression quality
      'stripMetadata'       => TRUE,
      'serveConverted'      => FALSE,
      'serveOriginalOnFail' => TRUE,
      'preferredConverters' => ['gd', 'webp', 'imagick'] // TODO: include 'thumbs.driver'
    ];

    // If config settings exist, return the config with fallback
    if(isset($settings) && array_key_exists($name, $settings)) {
      return c::get($prefix . $name, $settings[$name]);
    }
  }
}

foreach (settings::actions() as $action) {
  kirby()->hook('panel.file.' . $action, 'generateWebP');
}

function generateWebP($file) {

  try {

    // Checking file type since only images are processed
    if ($file->type() == 'image') {

      // Defining image-related options
      $input   = $file->dir() . '/' . $file->filename();
      $output  = $file->dir() . '/' . $file->name() . '.webp';
      $quality = settings::quality();
      $strip   = settings::stripMetadata();

      // Defining WebPConvert-related options
      WebPConvert::$serve_converted_image = settings::serveConverted();
      WebPConvert::$serve_original_image_on_fail = settings::serveOriginalOnFail();
      WebPConvert::set_preferred_converters(settings::preferredConverters());
      
      // Generating WebP image & placing it alongside the original version
      WebPConvert::convert($input, $output, $quality, $strip);
    }
  } catch (Exception $e) {
    return response::error($e->getMessage());
  }
}

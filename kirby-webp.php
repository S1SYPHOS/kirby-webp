<?php

/**
 * Kirby WebP - WebP generation for Kirby
 *
 * @package   Kirby CMS
 * @author    S1SYPHOS <hello@twobrain.io>
 * @link      http://twobrain.io
 * @version   0.4.0
 * @license   MIT
 */

if(!c::get('plugin.kirby-webp')) return;

// Initialising composer's autoloader
require_once  __DIR__ . DS . 'vendor' . DS . 'autoload.php';

// Loading settings & core
function webp() {
  require_once __DIR__ . DS . 'core' . DS . 'generate_webp.php';
  return new Kirby\Plugins\WebP\Convert;
}

$hooks = c::get('plugin.kirby-webp.hooks', ['upload']);

foreach ($hooks as $hook) {
  kirby()->hook('panel.file.' . $hook, function ($file) {
    webp()->generateWebP($file);
  });
}

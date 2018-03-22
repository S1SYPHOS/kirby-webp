<?php

/**
 * Kirby WebP - WebP generation for Kirby
 *
 * @package   Kirby CMS
 * @author    S1SYPHOS <hello@twobrain.io>
 * @link      http://twobrain.io
 * @version   0.5.0
 * @license   MIT
 */

if (c::get('plugin.kirby-webp', false)) {
    // Initialising composer's autoloader
    require_once __DIR__ . DS . 'vendor' . DS . 'autoload.php';

    // Loading settings & core
    require_once __DIR__ . DS . 'core' . DS . 'generate_webp.php';

    $hooks = c::get('plugin.kirby-webp.hooks', ['upload']);

    foreach ($hooks as $hook) {
        kirby()->hook('panel.file.' . $hook, function ($file) {
            (new Kirby\Plugins\WebP\Convert)->generateWebP($file);
        });
    }
}

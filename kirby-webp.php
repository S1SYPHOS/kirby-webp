<?php

/**
 * Kirby WebP - INSERT DESCRIPTION
 *
 * @package   Kirby CMS
 * @author    S1SYPHOS <hello@twobrain.io>
 * @link      http://twobrain.io
 * @version   0.1.0
 * @license   MIT
 */

if(!c::get('plugin.kirby-webp')) return;

// Initialising composer's autoloader
require_once  __DIR__ . DS . 'vendor' . DS . 'autoload.php';

// Loading settings & core
include_once __DIR__ . DS . 'core' . DS . 'generate_webp.php';

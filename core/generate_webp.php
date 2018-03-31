<?php

 namespace Kirby\Plugins\WebP;

 use c;
 use WebPConvert;

/*
 * TODO:
 *
 * $kirby->option('thumb.quality') && 'thumbs.driver'
 *
 */

class Convert
{
    protected $quality;
    protected $strip;
    protected $serve;
    protected $log;
    protected $converters;

    public function __construct()
    {
        $this->quality = c::get('plugin.kirby-webp.quality', 90);
        $this->strip = c::get('plugin.kirby-webp.stripMetadata', true);
        $this->serve = c::get('plugin.kirby-webp.convertedImage', true);
        $this->log = c::get('plugin.kirby-webp.serveOriginalOnFail', true);
        $this->converters = c::get('plugin.kirby-webp.converters', ['gd', 'cwebp']);
    }

    public function generateWebP($file)
    {
        try {
            // Checking file type since only images are processed
            if ($file->type() == 'image') {
                // WebPConvert options
                $path = $file->dir() . '/';
                $input   = $path . $file->filename();
                $output  = $path . $file->name() . '.webp';

                WebPConvert::$serve_original_image_on_fail = $this->serve;
                WebPConvert::$serve_converted_image = $this->log;
                WebPConvert::setPreferredConverters($this->converters);
                // WebPConvert::$debug = FALSE;

                // Generating WebP image & placing it alongside the original version
                WebPConvert::convert($input, $output, $this->quality, $this->strip);
            }
        } catch (Exception $e) {
            return response::error($e->getMessage());
        }
    }
}

# Kirby WebP
[![Release](https://img.shields.io/github/release/S1SYPHOS/kirby-webp.svg?color="brightgreen")](https://github.com/S1SYPHOS/kirby-webp/releases) [![License](https://img.shields.io/github/license/S1SYPHOS/kirby-webp.svg)](https://github.com/S1SYPHOS/kirby-webp/blob/master/LICENSE) [![Issues](https://img.shields.io/github/issues/S1SYPHOS/kirby-webp.svg)](https://github.com/S1SYPHOS/kirby-webp/issues)

This plugin generates WebP images alongside your uploaded JPGs & PNGs - so **you** don't have to.

**Table of contents**
- [1. What is it good for?](#whats-is-it-good-for)
- [2. Getting started](#getting-started)
- [3. Configuration](#configuration)
- [4. Options](#options)
- [5. Troubleshooting](#troubleshooting)
- [6. Credits / License](#credits--license)

## What is it good for?
Absolutely .. smaller image size:

> WebP is a method of lossy and lossless compression that can be used on a large variety of photographic, translucent and graphical images found on the web. The degree of lossy compression is adjustable so a user can choose the trade-off between file size and image quality. WebP typically achieves an average of 30% more compression than JPEG and JPEG 2000, without loss of image quality (see [Comparative Study](https://developers.google.com/speed/webp/docs/c_study)).
>
> [Google Developers](https://developers.google.com/speed/webp/faq)

Current [browser support](https://caniuse.com/#feat=webp) for WebP images is decent (Chrome, Opera & most mobile browsers), although heavily depending on your target region (ranging from North America (60%) & Europe (65%) to over 80% in Africa & South America).

For further information, including criticism, check out its [Wikipedia article](https://en.wikipedia.org/wiki/WebP). That being said, another interesting approach gearing towards the replacement of JPEG is Fabrice Bellard's "[Better Portable Graphics](https://bellard.org/bpg)" (BPG) format.

## Getting started
Use one of the following methods to install & use `kirby-webp`:

### Composer

```text
composer require S1SYPHOS/kirby-webp:dev-composer
```

### Git submodule

If you know your way around Git, you can download this plugin as a [submodule](https://github.com/blog/2104-working-with-submodules):

```text
git submodule add https://github.com/S1SYPHOS/kirby-webp.git site/plugins/kirby-webp
```

### Clone or download

1. [Clone](https://github.com/S1SYPHOS/kirby-webp.git) or [download](https://github.com/S1SYPHOS/kirby-webp/archive/master.zip) this repository.
2. Unzip / Move the folder to `site/plugins`.

### Activate the plugin
Activate the plugin with the following line in your `config.php`:

```text
c::set('plugin.kirby-webp', true);
```

## Configuration
After uploading some images, you are now officially ready to serve their newly generated WebP versions.

### Apache
If you're using [Apache](http://httpd.apache.org/) as your webserver, add the following lines to your `.htaccess`:

```text
<IfModule mod_rewrite.c>
  RewriteEngine On

  # Checking for WebP browser support ..
  RewriteCond %{HTTP_ACCEPT} image/webp

  # .. and if there's a WebP version for the requested image
  RewriteCond %{DOCUMENT_ROOT}/$1.webp -f

  # Well, then go for it & serve WebP instead
  RewriteRule (.+)\.(jpe?g|png)$ $1.webp [T=image/webp,E=accept:1]
</IfModule>

<IfModule mod_headers.c>
  Header append Vary Accept env=REDIRECT_accept
</IfModule>

<IfModule mod_mime.c>
  AddType image/webp .webp
</IfModule>
```

### NGINX
If you're using [NGINX](https://nginx.org/en/) as your webserver, add the following lines to your virtual host setup (for more information, go [here](https://github.com/uhop/grunt-tight-sprite/wiki/Recipe:-serve-WebP-with-nginx-conditionally) or [there](https://optimus.keycdn.com/support/configuration-to-deliver-webp)):

```text
// First, make sure that NGINX' `mime.types` file includes 'image/webp webp'
include /etc/nginx/mime.types;

// Checking if HTTP's `ACCEPT` header contains 'webp'
map $http_accept $webp_suffix {
  default "";
  "~*webp" ".webp";
}

server {
  // ...

  // Checking if there's a WebP version for the requested image ..
  location ~* ^.+\.(jpe?g|png)$ {
    add_header Vary Accept;
    // .. and if so, serving it
    try_files $1$webp_ext $uri =404;
  }
}
```

## Options
Change `kirby-webp` options to suit your needs:

| Option | Type | Default | Description |
| --- | --- | --- | --- |
| `plugin.kirby-webp.hooks` | Array | `['upload']` | Contains all `panel.file.*` [hooks](https://getkirby.com/docs/developer-guide/advanced/hooks) WebP generation should be applied to (allowed values are `upload`, `update`, `rename` and `replace`). |
| `plugin.kirby-webp.quality` | Integer | `90` | Defines WebP image quality (ranging from 0 to 100). |
| `plugin.kirby-webp.stripMetadata` | Boolean | `true` | Optionally enables / disables transfer of JPEG metadata onto the WebP image. |
| `plugin.kirby-webp.convertedImage` | Boolean | `true` | Optionally enables / disables output of converted image (`false` results in text output about the conversion process). |
| `plugin.kirby-webp.serveOriginalOnFail` | Boolean | `true` | Defines behavior in case all converters fail - by default, the original image will be served, whereas `false` will generate an image with the error message. |
| `plugin.kirby-webp.converters` | Array | `['gd', 'cwebp']` | Defines the desired order of execution for all supported converters (allowed values are `imagick`, `cwebp`, `gd` and `ewww`). Note that this only changes their order, but doesn't remove any of them. |

## Troubleshooting
Despite stating that `An unexpected error occurred`, WebP generation after renaming / updating images works - `replace` doesn't work at all .. PRs are always welcome :champagne:

Because of that, only `upload` is included by default. If you wish to investigate this further and / or don't care too much about the errror, go ahead with `c::set('plugin.webp.actions', ['upload', 'update', 'rename', 'replace']);` in your `config.php`.

## Credits / License
`kirby-webp` is based on Bjørn Rosell's [PHP library](https://github.com/rosell-dk/webp-convert) `webp-convert` library. It is licensed under the [MIT License](LICENSE), but **using Kirby in production** requires you to [buy a license](https://getkirby.com/buy). Are you ready for the [next step](https://getkirby.com/next)?

## Special Thanks
I'd like to thank everybody that's making great software - you people are awesome. Also I'm always thankful for feedback and bug reports :)

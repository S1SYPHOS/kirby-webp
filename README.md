# Kirby WebP
[![Release](https://img.shields.io/github/release/S1SYPHOS/kirby-webp.svg?color="brightgreen")](https://github.com/S1SYPHOS/kirby-webp/releases) [![License](https://img.shields.io/github/license/S1SYPHOS/kirby-webp.svg)](https://github.com/S1SYPHOS/kirby-webp/blob/master/LICENSE) [![Issues](https://img.shields.io/github/issues/S1SYPHOS/kirby-webp.svg)](https://github.com/S1SYPHOS/kirby-webp/issues)

This plugin generates `.webp` images alongside your uploaded `.jp(e)g` & `.png` versions - so **you** don't have to!

**Table of contents**
- [1. What is it good for?](#whats-is-it-good-for)
- [2. Getting started](#getting-started)
- [3. Configuration](#configuration)
- [4. Troubleshooting](#troubleshooting)
- [5. Credits / License](#credits--license)

## What is it good for?
Absolutely .. smaller image size!

## Getting started
Use one of the following methods to install & use `kirby-webp`:

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
If you're using [Apache](http://httpd.apache.org/) as your webserver, add the following lines to your `.htaccess` (right after `RewriteBase`):

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

## Troubleshooting
Despite stating that `An unexpected error occurred`, WebP generation after renaming / updating images works - `panel.file.replace` doesn't work at all .. PRs are always welcome :champagne:

Because of that, only `panel.file.upload` is included by default. If you wish to investigate this further or don't care too much about the errror, go head with `c::set('plugin.webp.actions', ['upload', 'update', 'replace']);` in your `config.php`.

## Credits / License
`kirby-webp` is based on Bjørn Rosell's `convert-webp` library. It is licensed under the [MIT License](LICENSE), but **using Kirby in production** requires you to [buy a license](https://getkirby.com/buy). Are you ready for the [next step](https://getkirby.com/next)?

## Special Thanks
I'd like to thank everybody that's making great software - you people are awesome. Also I'm always thankful for feedback and bug reports :)

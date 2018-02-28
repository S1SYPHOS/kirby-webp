# webp-convert
*Convert jpeg/png to webp with PHP (if at all possible)*

The state of webp conversion in PHP is currently as such: There are several ways to do it, but they all require *something* of the server-setup. What works on one shared host might not work on another.

This library is able to convert to webp using several methods. It will try one method after the other until success, or every method failed. You can set the desired order with the "preferred_converters" option.

Currently the following converters are available:

| Converter            | Method                                   | Summary                                              |
| -------------------- | ---------------------------------------- | ---------------------------------------------------- |
| [imagick](#imagick)  | Uses imagick extension                   | Best converter, but rarely available on shared hosts |
| [gd](#gd)            | Uses gd extension                        | Fast, but not able to do lossless encoding           |
| [cwebp](#cwebp)      | Calls cwebp binary directly              | Great, but requires ```exec()```                     |
| [ewww](#ewww)        | Calls EWWW Image Optimizer cloud service | Works on *almost* any shared webhost, slow, cheap, requires key |

## Usage

### Basic usage:
```php
include( __DIR__ . '/WebPConvert.php');

$source = $_SERVER['DOCUMENT_ROOT'] . '/images/subfolder/logo.jpg';
$destination = $_SERVER['DOCUMENT_ROOT'] . '/images/subfolder/logo.jpg.webp';
$quality = 90;
$strip_metadata = TRUE;

WebPConvert::$serve_converted_image = TRUE;
WebPConvert::$serve_original_image_on_fail = TRUE;
WebPConvert::set_preferred_converters(array('imagick','cwebp'));
WebPConvert::convert($source, $destination, $quality, $strip_metadata);
```


## API

### WebPConvert
*WebPConvert::convert($source, $destination, $quality, $strip_metadata)*\
- *$source:* (string) Absolute path to source image. Only forward slashes are allowed.\
- *$destination:* (string) Absolute path of the converted image. WebPConvert will take care of creating folders that does not exist. If there is already a file at the destination, it will be removed. Of course, it is required that the webserver has write permissions to the folder. Created folders will get the same permissions as the closest existing parent folder.\
- *$quality* (integer) Desired quality of output. Only relevant when source is a jpeg image. If source is a PNG, lossless encoding will be chosen.\
- *$strip_metadata* (bool) Whether to copy jpeg metadata to WebP (not all converters supports this)\

*WebPConvert::set_preferred_converters*\ (array)
Setting this manipulates the default order in which the converters are tried. If you for example set it to "cwebp", it means that you want "cwebp" to be tried first. You can specify several favourite converters. Setting it to "imagick,cwebp" will put imagick to the top of the list and cwebp will be the next converter to try, if imagick fails. The option will not remove any converters from the list, only change the order.

*WebPConvert::$serve_converted_image* (bool)\
If TRUE, the converted image will be output (served). Otherwise the script will produce text output about the convertion process.

*WebPConvert::$serve_original_image_on_fail* (bool)\
When WebPConvert is told to serve an image, but all converters fails to convert, WebPConvert looks at this option to decide what to do. If set to TRUE, WebPConvert will serve the original image. If set to FALSE, WebPConvert will generate an image with the error message. TRUE is probably a good choice on production servers while FALSE is probably a good choice on development servers.


## Converters

Each "method" of converting an image to webp are implemented as a separate converter. *WebPConvert* autodetects the converters by scanning the "converters" directory, so it is easy to add new converters, and safe to remove existing ones.

A converter simply consists of a convert function, which takes same arguments as *WebPConvert::convert*. The job of the converter is to convert *$source* to WebP and save it at *$destination*, preferrably taking *$quality* and *$strip_metadata* into account. It however relies on *WebPConvert* to take care of the following common tasks:
- *WebPConvert* checks that source file exists
- *WebPConvert* prepares a directory for the destination if it doesn't exist already
- *WebPConvert* checks that it will be possible to write a file at the destination
- *WebPConvert* checks whether the converter actually produced a file at the destination

Basically there are three types of converters. 
1. Those that are based on a php extension (for example gd or imagick)
2. Those that executes a binary directly using an exec() call
3. Those that connect to a cloud service which does the conversion

Converters based on a php extension should be your first choice. They run faster than the other methods and they don't need the server to allow exec() calls (which increases security risks). However, the *gd* converter does not support lossless convertion, so you may want to skip that for PNG's. Converters that executes a binary are also very fast (around than 50ms). Converters that delegates conversion to a cloud service are much slower (convertion takes about 1 second), but works on most shared hosts (as opposed to the other methods). This makes the cloud converters an ideal last resort. They generally requires *purchacing* a key, but the key for EWWW Image Optimizer is very cheap. Also note that there is a risk that a cloud converter has down-time. You can minimize the risk by setting up *two* cloud converters (once I get around adding more cloud converters)

#### imagick
*Best, but rarely available on shared hosts*

```Requirements..```: imagick extension compiled with WebP support<br>
```Speed.........```: Between 20 ms - 320 ms to convert a 40kb image depending on *WEBPCONVERT_IMAGICK_METHOD* setting<br>
```Reliability...```: I'm not aware of any problems<br>
```Availability..```: Probably only available on few shared hosts (if any)<br>

WebP convertion with imagick is fast and [imagick exposes many WebP options](http://www.imagemagick.org/script/webp.php). Unfortunately WebP support for the imagick extension is not at all out of the box. At least not on the systems I have tried (Ubuntu 16.04 and Ubuntu 17.04). But if installed, it works great and has several WebP options.

The converter supports:
- lossless encoding of PNG's.
- quality
- prioritize between quality and speed
- low memory option

You can configure the converter by defining any of the following constants:

*WEBPCONVERT_IMAGICK_METHOD*: This parameter controls the trade off between encoding speed and the compressed file size and quality. Possible values range from 0 to 6. When higher values are used, the encoder will spend more time inspecting additional encoding possibilities and decide on the quality gain. Lower value can result in faster processing time at the expense of larger file size and lower compression quality. Default value is 6 (higher than the default value of the cwebp command, which is 4).\
*WEBPCONVERT_IMAGICK_LOW_MEMORY*: The low memory option will make the encoding slower and the output slightly different in size and distortion. This flag is only effective for methods 3 and up. It is *on* by default. To turn it off, set the constant to ```FALSE```\


In order to get imagick with WebP on Ubuntu 16.04, you currently need to:
1. [Compile libwebp from source](https://developers.google.com/speed/webp/docs/compiling)
2. [Compile imagemagick from source](https://www.imagemagick.org/script/install-source.php) (```./configure --with-webp=yes```)
3. Compile php-imagick from source, phpize it and add ```extension=/path/to/imagick.so``` to php.ini

#### gd 
*Fast. But not good for PNG's*

```Requirements..```: GD extension and PHP > 5.5.0 compiled with WebP support<br>
```Speed.........```: Around 30 ms to convert a 40kb image<br>
```Reliability...```: Not sure. I have experienced corrupted images, but cannot reproduce<br>
```Availability..```: Unfortunately, according to [this link](https://stackoverflow.com/questions/25248382/how-to-create-a-webp-image-in-php), WebP support on shared hosts is rare.<br>

The converter does not support copying metadata.

*GD* unfortunately does not expose any WebP options. Lacking the option to set lossless encoding results in poor encoding of PNG's - the filesize is generally much larger than the original. For this reason, PNG conversion is *disabled* per default. The converter however lets you override this default by defining the *WEBPCONVERT_GD_PNG* constant.

Converter options:

*WEBPCONVERT_GD_PNG*: If set to TRUE, the converter will convert PNG's even though the result will be bad.


[imagewebp](http://php.net/manual/en/function.imagewebp.php) is a function that comes with PHP (>5.5.0) *provided* that PHP has been compiled with WebP support. Due to a [bug](https://bugs.php.net/bug.php?id=66590), some versions sometimes created corrupted images. That bug can however easily be fixed in PHP (fix was released [here](https://stackoverflow.com/questions/30078090/imagewebp-php-creates-corrupted-webp-files)). However, I have experienced corrupted images *anyway* (but cannot reproduce that bug). So use this converter with caution. The corrupted images shows as completely transparent images in Google Chrome, but with correct size.

To get WebP support for *gd* in PHP 5.5, PHP must be configured with the "--with-vpx-dir" flag. In PHP 7.0, php has to be configured with the "--with-webp-dir" flag [source](http://il1.php.net/manual/en/image.installation.php).



#### cwebp 
*Great, fast enough but requires exec()*

```Requirements..```: exec()<br>
```Speed.........```: Between 40 ms - 120 ms to convert a 40kb image depending on *WEBPCONVERT_CWEBP_METHOD* setting<br>
```Reliability...```: Great<br>
```Availability..```: exec() is available on surprisingly many webhosts, and the PHP solution by *EWWW Image Optimizer*, which this code is largely based on has been reported to work on many webhosts - [here is a list](https://wordpress.org/plugins/ewww-image-optimizer/#installation)<br>

[cwebp](https://developers.google.com/speed/webp/docs/cwebp) is a WebP convertion command line converter released by Google. A its core, our implementation looks in the /bin folder for a precompiled binary appropriate for the OS and executes it with [exec()](http://php.net/manual/en/function.exec.php). Thanks to Shane Bishop for letting me copy his precompilations which comes with his plugin, [EWWW Image Optimizer](https://ewww.io/). 

The converter supports:
- lossless encoding of PNG's.
- quality
- strip metadata
- prioritize between quality and speed
- low memory option

You can configure the converter by defining any of the following constants:

*WEBPCONVERT_CWEBP_METHOD*: This parameter controls the trade off between encoding speed and the compressed file size and quality. Possible values range from 0 to 6. When higher values are used, the encoder will spend more time inspecting additional encoding possibilities and decide on the quality gain. Lower value can result in faster processing time at the expense of larger file size and lower compression quality. Default value is 6 (higher than the default value of the cwebp command, which is 4).\
*WEBPCONVERT_CWEBP_LOW_MEMORY*: The low memory option will make the encoding slower and the output slightly different in size and distortion. This flag is only effective for methods 3 and up. It is *on* by default. To turn it off, set the constant to ```FALSE```

The cwebp command has more options, which can easily be implemented, if there is an interest. View the options [here](https://developers.google.com/speed/webp/docs/cwebp)



Official precompilations are available on [here](https://developers.google.com/speed/webp/docs/precompiled). But note that our script tests the checksum of the binary before executing it. This means that you cannot just replace a binary - you will have to change the checksum hardcoded in *converters/cwebp.php* too. If you find the need to use another binary, than those that comes with this project, please write - chances are that it should be added to the project.

In more detail, the implementation does this:
- Binary is selected form the bin-folder, according to OS
- If no binary is found, or if execution fails, try common system paths ('/usr/bin/cwebp' etc)
- Before executing binary, the checksum is tested
- Options are generated. -lossless is used for png. -metadata is set to "all" or "none"
- If "nice" command is found on host, then binary is run with low priority in order to save system ressources
- The permissions of the generated file is set to be the same as parent
- It is detected whether the command succeeds or not

Credits also goes to Shane regarding the code that revolves around the exec(). Most of it is a refactoring of the code in [EWWW Image Optimizer](https://ewww.io/).


#### ewww
*Cheap cloud service. Should work on *almost* any webhost. But slow.*

```Requirements..```: A valid key to [EWWW Image Optimizer](https://ewww.io/), curl and PHP >= 5.5<br>
```Speed.........```: Around 1300 ms to convert a 40kb image<br>
```Reliability...```: Great (but, as with any cloud service, there is a risk of downtime)<br>
```Availability..```: Should work on *almost* any webhost<br>

EWWW Image Optimizer is a very cheap cloud service for optimizing images.

You set up the key by defining the constant "WEBPCONVERT_EWW_KEY". Ie: ```define("WEBPCONVERT_EWW_KEY", "your_key_here")```;

The converter supports:
- lossless encoding of PNG's.
- quality
- metadata

The cloud service supports other options, which can easily be implemented, if there is an interest. View options [here](https://ewww.io/api/)

The converter could be improved by using *fsockopen* if *curl* is not available. This is however low priority as the curl extension is available on most shared hosts. PHP >= 5.5 is also widely available ([PHP 5.4 reached end of life more than a year ago](http://php.net/supported-versions.php)).




## The script

*webp-convert.php* can be used to serve converted images, or just convert without serving. It accepts the following parameters in the URL:

*source:*\
Path to source file. Can be absolute or relative (relative to document root). If it starts with "/", it is considered an absolute path.

*destination-root (optional):*\
The final destination will be calculated like this: [desired destination root] + [relative path of source file] + ".webp". If you want converted files to be put in the same folder as the originals, you can set destination-root to ".", or leave it blank. If you on the other hand want all converted files to reside in their own folder, set the destination-root to point to that folder. The converted files will be stored in a hierarchy that matches the source files. With destination-root set to "webp-cache", the source file "images/2017/cool.jpg" will be stored at "webp-cache/images/2017/cool.jpg.webp". Both absolute paths and relative paths are accepted (if the path starts with "/", it is considered an absolute path). Double-dots in paths are allowed, ie "../webp-cache"

*quality:*\
The quality of the generated WebP image, 0-100.

*strip-metadata:*\
If set (if "&strip-metadata" is appended to the url), metadata will not be copied over in the conversion process. Note however that not all converters supports copying metadata. cwebp supports it, imagewebp does not. You can also assign a value. Any value but "no" counts as yes

*preferred-converters (optional):*\
Setting this manipulates the default order in which the converters are tried. If you for example set it to "cwebp", it means that you want "cwebp" to be tried first. You can specify several favourite converters. Setting it to "cwebp,imagewebp" will put cwebp to the top of the list and imagewebp will be the next converter to try, if cwebp fails. The option will not remove any converters from the list, only change the order.

*serve-image (optional):*\
If set (if "&serve-image" is appended to the URL), the converted image will be served. Otherwise the script will produce text output about the convertion process. You can also assign a value. Any value but "no" counts as yes. Note: If debug is enabled, text will always be output

*debug (optional):*\
Enabling debug has two functions:\
1) It will always return text (serve-image setting is overriden)
2) PHP error reporting is turned on

*serve-original-image-on-fail (optional):*\
Default: "yes". Decides what action to take in the situation that (1) all converters fails to convert the image, and (2) WebPConvert is told to serve the converted image. the original image. Default action is to serve the *original* image. End-users will not notice the fail, which is good on production servers, but not on development servers. If set to "no", WebPConvert will instead generate an image containing the error message.

## WebPConvertPathHelper

This helper is used by the script in order to make it accept relative urls and to operate with a "destination root". You will probably not need it, but in case you are interested, it is documented [here, on the wiki](https://github.com/rosell-dk/webp-convert/wiki/WebPConvertPathHelper)


## SECURITY
TODO! - The script does not currently sanitize values.

## Roadmap
* Sanitize
* gd should not convert png, unless option set to do so







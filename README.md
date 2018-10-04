# Assets middleware 

This PHP 7.1 library is a [PSR-15](https://www.php-fig.org/psr/psr-15/) middleware dedicated to manage static assets like CSS, JS, or image files.

## Usage

```php
<?php
use CodeInc\AssetsMiddleware\AssetsMiddleware;

$assetsMiddleware = new AssetsMiddleware(
    '/assets/' // <-- specifies the assets base URI path
); 

// adding web assets directories
$assetsMiddleware->addAssetsDirectory('/path/to/my/first/web-assets-directory');
$assetsMiddleware->addAssetsDirectory('/path/to/another/web-assets-directory');

// optionally you can limit the acceptable media types
$assetsMiddleware->setAllowMediaTypes([
    'image/*',
    'text/css',
    'application/javascript'
]);

// returns the computed path to the assets directory
$assetsMiddleware->getAssetUri('/path/to/another/web-assets-directory/an-image.jpg');

// processed a PSR-7 server request as a PSR-15 middleware
$assetsMiddleware->process($aPsr7ServerRequest, $aPsr15RequestHandler); // <-- returns a PSR-7 response
```

## Installation

This library is available through [Packagist](https://packagist.org/packages/codeinc/assets-middleware) and can be installed using [Composer](https://getcomposer.org/): 

```bash
composer require codeinc/assets-middleware
```


## License

The library is published under the MIT license (see [`LICENSE`](LICENSE) file).
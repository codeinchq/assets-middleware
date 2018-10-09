# Assets middleware 

This PHP 7.1 library is a [PSR-15](https://www.php-fig.org/psr/psr-15/) middleware dedicated to manage static assets like CSS, JS, or image files.

## Usage

```php
<?php
use CodeInc\AssetsMiddleware\AssetsMiddleware;
use CodeInc\AssetsMiddleware\Resolvers\AssetsDirectoryResolver;

$assetsMiddleware = new AssetsMiddleware(
    new AssetsDirectoryResolver(
        '/path/to/my/assets/assets/', // <-- directory path
        '/assets/' // <-- assets URI prefix
    )
);
// optionally you can limit the acceptable media types
$assetsMiddleware->setAllowMediaTypes([
    'image/*', // supports shell patterns through fnmatch()
    'text/css',
    'application/javascript'
]);

// processed a PSR-7 server request as a PSR-15 middleware
$assetsMiddleware->process($aPsr7ServerRequest, $aPsr15RequestHandler); // <-- returns a PSR-7 response
```

### Using multiple resolvers

```php
<?php
use CodeInc\AssetsMiddleware\AssetsMiddleware;
use CodeInc\AssetsMiddleware\Resolvers\AssetsDirectoryResolver;
use CodeInc\AssetsMiddleware\Resolvers\StaticAssetsResolver;
use CodeInc\AssetsMiddleware\Resolvers\AssetResolverAggregator;

$assetsMiddleware = new AssetsMiddleware(
    new AssetResolverAggregator([
        new StaticAssetsResolver(['/favicon.ico' => '/local/favicon/file.ico']),
        new AssetsDirectoryResolver('/path/to/my/css/', '/css/'),
        new AssetsDirectoryResolver('/path/to/my/images/', '/images/')
    ])
);

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
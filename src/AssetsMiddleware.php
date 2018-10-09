<?php
//
// +---------------------------------------------------------------------+
// | CODE INC. SOURCE CODE                                               |
// +---------------------------------------------------------------------+
// | Copyright (c) 2018 - Code Inc. SAS - All Rights Reserved.           |
// | Visit https://www.codeinc.fr for more information about licensing.  |
// +---------------------------------------------------------------------+
// | NOTICE:  All information contained herein is, and remains the       |
// | property of Code Inc. SAS. The intellectual and technical concepts  |
// | contained herein are proprietary to Code Inc. SAS are protected by  |
// | trade secret or copyright law. Dissemination of this information or |
// | reproduction of this material is strictly forbidden unless prior    |
// | written permission is obtained from Code Inc. SAS.                  |
// +---------------------------------------------------------------------+
//
// Author:   Joan Fabrégat <joan@codeinc.fr>
// Date:     05/03/2018
// Time:     17:15
// Project:  AssetsMiddleware
//
declare(strict_types = 1);
namespace CodeInc\AssetsMiddleware;
use CodeInc\AssetsMiddleware\Assets\AssetInterface;
use CodeInc\AssetsMiddleware\Exceptions\InvalidAssetMediaTypeException;
use CodeInc\AssetsMiddleware\Exceptions\ResponseErrorException;
use CodeInc\AssetsMiddleware\Resolvers\AssetResolverInterface;
use CodeInc\AssetsMiddleware\Responses\AssetResponse;
use CodeInc\AssetsMiddleware\Responses\AssetResponseInterface;
use CodeInc\AssetsMiddleware\Responses\AssetMinifiedResponse;
use CodeInc\AssetsMiddleware\Responses\NotModifiedAssetResponse;
use Micheh\Cache\CacheUtil;
use function PHPSTORM_META\elementType;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


/**
 * Class AssetsMiddleware
 *
 * @package CodeInc\AssetsMiddleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 * @license MIT <https://github.com/CodeIncHQ/AssetsMiddleware/blob/master/LICENSE>
 * @link https://github.com/CodeIncHQ/AssetsMiddleware
 */
class AssetsMiddleware implements MiddlewareInterface
{
    /**
     * @var AssetResolverInterface
     */
    private $resolver;

    /**
     * Base assets URI path.
     *
     * @var string
     */
    private $assetsUriPrefix;

    /**
     * Allows the assets to the cached in the web browser.
     *
     * @var bool
     */
    private $cacheAssets;

    /**
     * Allows the assets to be minimized.
     *
     * @var bool
     */
    private $minifyAssets;

    /**
     * Limits the allowed assets media types.
     *
     * @var null|string[]
     */
    private $allowedMediaTypes;

    /**
     * AssetsMiddleware constructor.
     *
     * @param AssetResolverInterface $resolver
     * @param string $assetsUriPrefix Base assets URI path
     * @param bool $cacheAssets Allows the assets to the cached in the web browser
     * @param bool $minifyAssets Minimizes the assets before sending them (@see AssetCompressedResponse)
     */
    public function __construct(AssetResolverInterface $resolver, string $assetsUriPrefix,
        bool $cacheAssets = true, bool $minifyAssets = false)
    {
        $this->resolver = $resolver;
        $this->assetsUriPrefix = $assetsUriPrefix;
        $this->cacheAssets = $cacheAssets;
        $this->minifyAssets = $minifyAssets;
    }

    /**
     * Sets the allowed media types for the assets. The comparison supports shell patterns with operators
     * like *, ?, etc.
     *
     * @param iterable $allowMediaTypes
     */
    public function setAllowMediaTypes(iterable $allowMediaTypes):void
    {
        $this->allowedMediaTypes = ($allowMediaTypes instanceof \Traversable)
            ? iterator_to_array($allowMediaTypes)
            : $allowMediaTypes;
    }

    /**
     * @return null|string[]
     */
    public function getAllowedMediaTypes():?array
    {
        return $this->allowedMediaTypes;
    }

    /**
     * @inheritdoc
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return AssetResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
    {
        $reqUriPath = $request->getUri()->getPath();
        if (preg_match('/^'.preg_quote($this->assetsUriPrefix, '/').'.+$/ui', $reqUriPath)
            && ($asset = $this->resolver->getAsset($reqUriPath)) !== null)
        {
            try {
                // checking the asset's media type
                if (!$this->isMediaTypeAllowed($asset)) {
                    throw new InvalidAssetMediaTypeException($asset);
                }

                // building the response
                $response = $this->minifyAssets
                    ? new AssetMinifiedResponse($asset)
                    : new AssetResponse($asset);

                // enabling cache
                if ($this->cacheAssets && ($mTime = $asset->getMTime()) !== null) {
                    $cache = new CacheUtil();
                    $response = $cache->withCache($response, true, 3600);
                    $response = $cache->withETag($response, hash('sha1', (string)$mTime->getTimestamp()));
                    $response = $cache->withLastModified($response, $mTime);
                    if ($cache->isNotModified($request, $response)) {
                        $response = new NotModifiedAssetResponse($asset);
                    }
                    return $response;
                }
                return $response;
            }
            catch (\Throwable $exception) {
                throw new ResponseErrorException($asset, 0, $exception);
            }
        }

        return $handler->handle($request);
    }


    /**
     * Verifies if the assets media type is supported.
     *
     * @param AssetInterface $asset
     * @return bool
     */
    protected function isMediaTypeAllowed(AssetInterface $asset):bool
    {
        if (is_array($this->allowedMediaTypes)) {
            foreach ($this->allowedMediaTypes as $allowedMediaType) {
                if (fnmatch($allowedMediaType, $asset->getMediaType())) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    /**
     * Returns the assets resolver in use.
     *
     * @return AssetResolverInterface
     */
    public function getResolver():AssetResolverInterface
    {
        return $this->resolver;
    }
}
<?php
//
// +---------------------------------------------------------------------+
// | CODE INC. SOURCE CODE                                               |
// +---------------------------------------------------------------------+
// | Copyright (c) 2017 - Code Inc. SAS - All Rights Reserved.           |
// | Visit https://www.codeinc.fr for more information about licensing.  |
// +---------------------------------------------------------------------+
// | NOTICE:  All information contained herein is, and remains the       |
// | property of Code Inc. SAS. The intellectual and technical concepts  |
// | contained herein are proprietary to Code Inc. SAS are protected by  |
// | trade secret or copyright law. Dissemination of this information or |
// | reproduction of this material  is strictly forbidden unless prior   |
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
use CodeInc\AssetsMiddleware\Assets\AssetCompressedResponse;
use CodeInc\AssetsMiddleware\Assets\AssetNotModifiedResponse;
use CodeInc\AssetsMiddleware\Assets\AssetResponse;
use CodeInc\AssetsMiddleware\Test\AssetsMiddlewareTest;
use Micheh\Cache\CacheUtil;
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
 * @see AssetsMiddlewareTest
 */
class AssetsMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $assetsLocalPath;

    /**
     * @var string
     */
    private $assetsUriPath;

    /**
     * @var bool
     */
    private $allowAssetsCache;

    /**
     * @var bool
     */
    private $allowAssetsCompression;

    /**
     * AssetsMiddleware constructor.
     *
     * @param string $assetsLocalPath
     * @param string $assetsUriPath
     * @param bool $allowAssetsCache Allows assets cache through HTTP headers
     * @param bool $allowAssetsCompression Compresses CSS, JS and SVG files
     * @throws AssetsMiddlewareException
     */
    public function __construct(string $assetsLocalPath, string $assetsUriPath,
        bool $allowAssetsCache = true, bool $allowAssetsCompression = false)
    {
        if (!is_dir($assetsLocalPath) || ($assetsLocalPath = realpath($assetsLocalPath)) === null) {
            throw new AssetsMiddlewareException(
                sprintf("%s is not a directory and can not be used as assets source", $assetsLocalPath),
                $this
            );
        }
        $this->assetsLocalPath = $assetsLocalPath;
        $this->assetsUriPath = $assetsUriPath;
        $this->allowAssetsCache = $allowAssetsCache;
        $this->allowAssetsCompression = $allowAssetsCompression;
    }

    /**
     * @inheritdoc
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \CodeInc\MediaTypes\Exceptions\MediaTypesException
     * @throws \CodeInc\Psr7Responses\ResponseException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
    {
        // if the response points toward a valid asset
        if (($assetName = $this->getAssetName($request)) !== null) {
            $assetPath = $this->getAssetPath($assetName);
            if (file_exists($assetPath)) {

                // builds the response
                if (!$this->allowAssetsCompression) {
                    $response = new AssetResponse($assetPath, $assetName);
                }
                else {
                    $response = new AssetCompressedResponse($assetPath, $assetName);
                }

                // enables the cache
                if ($this->allowAssetsCache) {
                    $assetMTime = filemtime($assetPath);
                    $cache = new CacheUtil();
                    $response = $cache->withCache($response, true, 3600);
                    $response = $cache->withETag($response, hash('sha1', (string)$assetMTime));
                    $response = $cache->withLastModified($response, $assetMTime);
                    if ($cache->isNotModified($request, $response)) {
                        return new AssetNotModifiedResponse($assetName);
                    }
                }

                return $response;
            }
        }

        // returns the handler response
        return $handler->handle($request);
    }

    /**
     * @return string
     */
    public function getAssetsLocalPath():string
    {
        return $this->assetsLocalPath;
    }

    /**
     * @return string
     */
    public function getAssetsUriPath():string
    {
        return $this->assetsUriPath;
    }

    /**
     * Enables the assets cache (enabled by default).
     */
    public function enableAssetsCache():void
    {
        $this->allowAssetsCache = false;
    }

    /**
     * Disables the assets cache (enabled by default).
     */
    public function disableAssetsCache():void
    {
        $this->allowAssetsCache = false;
    }

    /**
     * Returns an asset's name from a request or null if the request does'nt points toward an asset.
     *
     * @param ServerRequestInterface $request
     * @return null|string
     */
    public function getAssetName(ServerRequestInterface $request):?string
    {
        if (preg_match('#^'.preg_quote($this->assetsUriPath, '#').'([\\w\\-_./]+)$#ui',
            $request->getUri()->getPath(), $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Returns an asset's path.
     *
     * @param string $assetName
     * @return string
     */
    public function getAssetPath(string $assetName):string
    {
        if (substr($assetName, 0, strlen(DIRECTORY_SEPARATOR)) == DIRECTORY_SEPARATOR) {
            $assetName = substr($assetName, strlen(DIRECTORY_SEPARATOR));
        }
        return $this->assetsLocalPath.DIRECTORY_SEPARATOR.$assetName;
    }

    /**
     * Returns an assets URI path.
     *
     * @param string $asset
     * @return string
     */
    public function getAssetUriPath(string $asset):string
    {
        return $this->assetsUriPath.$asset;
    }
}
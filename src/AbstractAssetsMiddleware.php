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
use CodeInc\AssetsMiddleware\Exceptions\ResponseErrorException;
use CodeInc\AssetsMiddleware\Responses\MinifiedAssetResponse;
use CodeInc\AssetsMiddleware\Responses\AssetResponseInterface;
use CodeInc\AssetsMiddleware\Responses\NotModifiedAssetResponse;
use CodeInc\AssetsMiddleware\Responses\AssetResponse;
use Micheh\Cache\CacheUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Validator\File\Exists;


/**
 * Class AbstractAssetsMiddleware
 *
 * @package CodeInc\AssetsMiddleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 * @license MIT <https://github.com/CodeIncHQ/AssetsMiddleware/blob/master/LICENSE>
 * @link https://github.com/CodeIncHQ/AssetsMiddleware
 */
abstract class AbstractAssetsMiddleware implements MiddlewareInterface
{
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
    private $minimizeAssets;

    /**
     * AssetsMiddleware constructor.
     *
     * @param string $assetsUriPrefix Base assets URI path
     * @param bool $cacheAssets Allows the assets to the cached in the web browser
     * @param bool $minimizeAssets Minimizes the assets before sending them (@see AssetCompressedResponse)
     */
    public function __construct(string $assetsUriPrefix, bool $cacheAssets = true,
        bool $minimizeAssets = false)
    {
        $this->assetsUriPrefix = $assetsUriPrefix;
        $this->cacheAssets = $cacheAssets;
        $this->minimizeAssets = $minimizeAssets;
    }

    /**
     * Returns the assets directories. Keys must be directories identifiers (often hashes of the path)
     * and values directories paths.
     *
     * @return iterable
     */
    abstract protected function getAssetsDirectories():iterable;

    /**
     * @inheritdoc
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return AssetResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
    {
        // if the requests points toward an assets directory
        if (preg_match('#^'.preg_quote($this->assetsUriPrefix, '#').'([^/]+)/(.+)$#i',
            $request->getUri()->getPath(), $matches)) {

            // searching for the corresponding assets directory
            foreach ($this->getAssetsDirectories() as $directoryKey => $directoryPath) {

                // if a match is found
                if ($matches[1] == $directoryKey) {

                    // validating the assets location
                    $assetPath = $directoryPath.DIRECTORY_SEPARATOR.$matches[2];
                    if ((new Exists($directoryPath))->isValid($assetPath)) {

                        try {
                            // building the response
                            $response = $this->minimizeAssets
                                ? new MinifiedAssetResponse($assetPath) :
                                new AssetResponse($assetPath);

                            // enabling cache
                            if ($this->cacheAssets) {
                                $assetMTime = filemtime($assetPath);
                                $cache = new CacheUtil();
                                $response = $cache->withCache($response, true, 3600);
                                $response = $cache->withETag($response, hash('sha1', (string)$assetMTime));
                                $response = $cache->withLastModified($response, $assetMTime);
                                if ($cache->isNotModified($request, $response)) {
                                    $response = new NotModifiedAssetResponse($assetPath);
                                }
                            }
                        }
                        catch (\Throwable $exception) {
                            throw new ResponseErrorException($assetPath, 0, $exception);
                        }

                        return $response;
                    }
                }
            }
        }

        return $handler->handle($request);
    }

    /**
     * Returns the public URI for a given asset. The asset must be within a registered assets directory.
     *
     * @param string $assetPath
     * @return string
     */
    public function getAssetUri(string $assetPath):?string
    {
        foreach ($this->getAssetsDirectories() as $directoryKey => $directoryPath) {
            if (substr($assetPath, 0, strlen($directoryPath)) == $directoryPath) {
                return $this->assetsUriPrefix.urlencode($directoryKey)
                    .str_replace('\\', '/', substr($assetPath, strlen($directoryPath)));
            }
        }
        return null;
    }
}
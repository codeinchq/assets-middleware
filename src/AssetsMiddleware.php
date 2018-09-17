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
use CodeInc\AssetsMiddleware\Assets\AssetCompressedResponse;
use CodeInc\AssetsMiddleware\Assets\AssetResponseInterface;
use CodeInc\AssetsMiddleware\Assets\AssetNotModifiedResponse;
use CodeInc\AssetsMiddleware\Assets\AssetResponse;
use CodeInc\AssetsMiddleware\Test\AssetsMiddlewareTest;
use Micheh\Cache\CacheUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Validator\File\Exists;


/**
 * Class AssetsMiddleware
 *
 * @package CodeInc\AssetsMiddleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 * @license MIT <https://github.com/CodeIncHQ/AssetsMiddleware/blob/master/LICENSE>
 * @link https://github.com/CodeIncHQ/AssetsMiddleware
 * @see AssetsMiddlewareTest
 * @version 2
 */
class AssetsMiddleware implements MiddlewareInterface
{
    /**
     * Stack of local assets directories.
     *
     * @see AssetsMiddleware::registerAssetsDirectory()
     * @var string[]
     */
    private $assetsDirectories = [];

    /**
     * Base assets URI path.
     *
     * @var string
     */
    private $assetsUri;

    /**
     * Allows the assets to the cached in the web browser.
     *
     * @var bool
     */
    private $allowAssetsCache;

    /**
     * Allows the assets to be minimized.
     *
     * @var bool
     */
    private $allowAssetsMinimization;

    /**
     * AssetsMiddleware constructor.
     *
     * @param string $assetsUri Base assets URI path
     * @param bool $allowAssetsCache Allows the assets to the cached in the web browser
     * @param bool $allowAssetsMinimization Allows the assets to be minimized
     */
    public function __construct(string $assetsUri, bool $allowAssetsCache = true,
        bool $allowAssetsMinimization = false)
    {
        $this->assetsUri = $assetsUri;
        $this->allowAssetsCache = $allowAssetsCache;
        $this->allowAssetsMinimization = $allowAssetsMinimization;
    }

    /**
     * @inheritdoc
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface|AssetResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
    {
        // if the response points toward a valid asset
        if ($assetPath = $this->getAssetPath($request))
        {
            try {
                // builds the response
                if (!$this->allowAssetsMinimization) {
                    $response = new AssetResponse($assetPath);
                }
                else {
                    $response = new AssetCompressedResponse($assetPath);
                }

                // enables the cache
                if ($this->allowAssetsCache) {
                    $assetMTime = filemtime($assetPath);
                    $cache = new CacheUtil();
                    $response = $cache->withCache($response, true, 3600);
                    $response = $cache->withETag($response, hash('sha1', (string)$assetMTime));
                    $response = $cache->withLastModified($response, $assetMTime);
                    if ($cache->isNotModified($request, $response)) {
                        return new AssetNotModifiedResponse($assetPath);
                    }
                }

                return $response;
            }
            catch (\Throwable $exception) {
                throw new \RuntimeException(sprintf("Error while building the PSR-7 response "
                    ."for the asset request '%s'", $request->getUri()->getPath()));
            }
        }

        // returns the handler response
        return $handler->handle($request);
    }

    /**
     * Returns the asset path corresponding to a request.
     *
     * @param ServerRequestInterface $request
     * @return null|string
     */
    protected function getAssetPath(ServerRequestInterface $request):?string
    {
        // passing the request uri path
        if ($parsedUriPath = $this->parseAssetUriPath($request)) {

            // checking the assets parent directory
            if ($directoryPath = $this->getAssetsDirectoryPath($parsedUriPath['directoryHash'])) {

                // checking the asset existence
                $assetPath = $directoryPath.DIRECTORY_SEPARATOR.$parsedUriPath['assetPath'];
                if ((new Exists($directoryPath))->isValid($assetPath)) {
                    return $assetPath;
                }
            }
        }
        return null;
    }

    /**
     * Parsed a request URI path and if the URL is an assets, returns the asset's parent directory hash and path in an
     * associative array. Returns NULL if the requests does not points toward an asset.
     *
     * @param ServerRequestInterface $request
     * @return array|null
     */
    protected function parseAssetUriPath(ServerRequestInterface $request):?array
    {
        if (preg_match('#^'.preg_quote($this->assetsUri, '#').'([a-f0-9]{32})/(.+)$#i',
                $request->getUri()->getPath(), $matches)) {
            return [
                'directoryHash' => $matches[1],
                'assetPath' => $matches[2]
            ];
        }
        return null;
    }

    /**
     * @param string $directoryHash
     * @return null|string
     */
    protected function getAssetsDirectoryPath(string $directoryHash):?string
    {
        if (($directoryPath = array_search($directoryHash, $this->assetsDirectories)) !== false) {
            return $directoryPath;
        }
        return null;
    }

    /**
     * Registers multiple paths to web assets directories.
     *
     * Attention : all files in the directories will be publicly available.
     *
     * @uses AssetsMiddleware::registerAssetsDirectory()
     * @param iterable $directories
     */
    public function registerAssetsDirectories(iterable $directories):void
    {
        foreach ($directories as $directory) {
            $this->registerAssetsDirectory((string)$directory);
        }
    }

    /**
     * Registers a path to a web assets directory.
     *
     * Attention : all files in the directory will be publicly available.
     *
     * The method returns the base URI of the where the web assets within the directory will be available. This URI
     * can then be resolved using getAssetUri() and getAssetsDirUri().
     *
     * @param string $assetsDirectory
     * @return string
     */
    public function registerAssetsDirectory(string $assetsDirectory):string
    {
        if (!is_dir($assetsDirectory) || ($assetsDirectory = realpath($assetsDirectory)) === false) {
            throw new \RuntimeException(sprintf(_("The web assets path '%s' is not a directory"),
                $assetsDirectory));
        }
        if (!is_readable($assetsDirectory)) {
            throw new \RuntimeException(sprintf(_("The web assets directory '%s' is not readable"),
                $assetsDirectory));
        }
        if (isset($this->assetsDirectories[$assetsDirectory])) {
            throw new \LogicException(sprintf(_("The web assets directory '%s' is already registered"),
                $assetsDirectory));
        }

        $this->assetsDirectories[$assetsDirectory] = md5($assetsDirectory);

        return $this->getAssetsDirectoryUri($assetsDirectory);
    }

    /**
     * Returns the base URI path corresponding to a registered web asset's directory.
     *
     * @param string $assetsDir
     * @return string
     */
    public function getAssetsDirectoryUri(string $assetsDir):string
    {
        if (!isset($this->assetsDirectories[$assetsDir])) {
            throw new \RuntimeException(sprintf(_("The assets directory '%s' is no registered"),
                $assetsDir));
        }
        return $this->assetsUri.$this->assetsDirectories[$assetsDir].'/';
    }

    /**
     * Returns the public URI for a given asset. The asset must be within a registered assets directory.
     *
     * @param string $assetPath
     * @return string
     */
    public function getAssetUri(string $assetPath):string
    {
        if (($assetPath = realpath($assetPath)) === false) {
            throw new \LogicException(sprintf(_("Unable to read the real path of the asset '%s'"),
                $assetPath));
        }

        foreach ($this->assetsDirectories as $assetsDirectory => $directoryUuid) {
            if (substr($assetPath, 0, strlen($assetsDirectory)) == $assetsDirectory) {
                return $this->getAssetsDirectoryUri($assetsDirectory).substr($assetPath, strlen($assetsDirectory));
            }
        }

        // is the asset is not in any directory, throwing an exception
        throw new \LogicException(sprintf(_("The asset '%s' is not within any registered assets directory"),
            $assetPath));
    }

    /**
     * Returns the array of registered assets directories paths.
     *
     * @return string[]
     */
    public function getAssetsDirectories():array
    {
        return array_keys($this->assetsDirectories);
    }

    /**
     * Returns the assets base URI.
     *
     * @return string
     */
    public function getAssetsUri():string
    {
        return $this->assetsUri;
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
}
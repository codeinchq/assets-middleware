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
// Date:     09/10/2018
// Project:  AssetsMiddleware
//
declare(strict_types=1);
namespace CodeInc\AssetsMiddleware\Resolvers;
use CodeInc\AssetsMiddleware\Assets\AssetInterface;
use CodeInc\AssetsMiddleware\Assets\LocalAsset;
use CodeInc\AssetsMiddleware\Exceptions\NotAnAssetException;


/**
 * Class StaticAssetsResolver
 *
 * @package CodeInc\AssetsMiddleware\Resolvers
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class StaticAssetsResolver implements AssetResolverInterface, \IteratorAggregate, \Countable
{
    /**
     * @var string[]
     */
    private $assets = [];

    /**
     * StaticAssetsResolver constructor.
     *
     * @param iterable|null $assets
     * @throws NotAnAssetException
     */
    public function __construct(?iterable $assets = null)
    {
        if ($assets) {
            $this->addAssets($assets);
        }
    }

    /**
     * Adds multiple assets.
     *
     * @param iterable $assets
     * @throws NotAnAssetException
     */
    public function addAssets(iterable $assets):void
    {
        foreach ($assets as $uri => $path) {
            $this->addAsset($uri, $path);
        }
    }

    /**
     * Adds an asset.
     *
     * @param string $assetUri
     * @param string $assetPath
     * @throws NotAnAssetException
     */
    public function addAsset(string $assetUri, string $assetPath):void
    {
        if (($realAssetPath = realpath($assetPath)) === false) {
            throw new NotAnAssetException($assetPath);
        }
        $this->assets[$assetUri] = $realAssetPath;
    }

    /**
     * @inheritdoc
     * @param string $assetUri
     * @return AssetInterface|null
     * @throws \CodeInc\MediaTypes\Exceptions\MediaTypesException
     */
    public function getAsset(string $assetUri):?AssetInterface
    {
        if (array_key_exists($assetUri, $this->assets)) {
            return new LocalAsset($this->assets[$assetUri]);
        }
        return null;
    }

    /**
     * @inheritdoc
     * @param string $assetPath
     * @return null|string
     */
    public function getAssetUri(string $assetPath):?string
    {
        if (($realAssetPath = realpath($assetPath)) !== false) {
            foreach ($this->assets as $uri => $path) {
                if ($path == $realAssetPath) {
                    return $uri;
                }
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     * @return \ArrayIterator
     */
    public function getIterator():\ArrayIterator
    {
        return new \ArrayIterator($this->assets);
    }

    /**
     * @inheritdoc
     * @return int
     */
    public function count():int
    {
        return count($this->assets);
    }
}
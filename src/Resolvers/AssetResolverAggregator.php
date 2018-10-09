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
use CodeInc\AssetsMiddleware\Exceptions\NotAnAssetResolverException;
use CodeInc\CollectionInterface\CountableCollectionInterface;


/**
 * Class ResolverAggregator
 *
 * @package CodeInc\AssetsMiddleware\Resolvers
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class AssetResolverAggregator implements AssetResolverInterface, CountableCollectionInterface
{
    /**
     * @var AssetResolverInterface[]
     */
    private $resolvers = [];

    /**
     * @var int
     */
    private $iteratorPosition = 0;

    /**
     * AssetResolverAggregator constructor.
     *
     * @param iterable|AssetResolverInterface[]|null $resolvers
     * @throws NotAnAssetResolverException
     */
    public function __construct(?iterable $resolvers = null)
    {
        if ($resolvers) {
            $this->addResolvers($resolvers);
        }
    }

    /**
     * Adds multiple resolvers.
     *
     * @param iterable|AssetResolverInterface[] $resolvers
     * @throws NotAnAssetResolverException
     */
    public function addResolvers(iterable $resolvers):void
    {
        foreach ($resolvers as $resolver) {
            if (!$resolver instanceof AssetResolverInterface) {
                throw new NotAnAssetResolverException($resolver);
            }
            $this->addResolver($resolver);
        }
    }

    /**
     * Adds a resolver.
     *
     * @param AssetResolverInterface $resolver
     */
    public function addResolver(AssetResolverInterface $resolver):void
    {
        $this->resolvers[] = $resolver;
    }

    /**
     * Returns the asset corresponding to a given route.
     *
     * @param string $assetUri
     * @return AssetInterface|null
     */
    public function getAsset(string $assetUri):?AssetInterface
    {
        foreach ($this->resolvers as $resolver) {
            if (($asset = $resolver->getAsset($assetUri)) !== null) {
                return $asset;
            }
        }
        return null;
    }

    /**
     * Returns the URI of an asset.
     *
     * @param string $assetPath
     * @return null|string
     */
    public function getAssetUri(string $assetPath):?string
    {
        foreach ($this->resolvers as $resolver) {
            if (($uri = $resolver->getAssetUri($assetPath)) !== null) {
                return $uri;
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function rewind():void
    {
        $this->iteratorPosition = 0;
    }

    /**
     * @inheritdoc
     */
    public function next():void
    {
        $this->iteratorPosition++;
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function valid():bool
    {
        return array_key_exists($this->iteratorPosition, $this->resolvers);
    }

    /**
     * @inheritdoc
     * @return AssetResolverInterface
     */
    public function current():AssetResolverInterface
    {
        return $this->resolvers[$this->iteratorPosition];
    }

    /**
     * @inheritdoc
     * @return int
     */
    public function key():int
    {
        return $this->iteratorPosition;
    }

    /**
     * @inheritdoc
     * @return int
     */
    public function count():int
    {
        return count($this->resolvers);
    }
}
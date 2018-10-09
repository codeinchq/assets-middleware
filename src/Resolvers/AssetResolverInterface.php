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


/**
 * Interface AssetResolverInterface
 *
 * @package CodeInc\AssetsMiddleware\Resolvers
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
interface AssetResolverInterface
{
    /**
     * Returns the asset corresponding to a given route.
     *
     * @param string $assetUri
     * @return AssetInterface|null
     */
    public function getAsset(string $assetUri):?AssetInterface;

    /**
     * Returns the URI of an asset.
     *
     * @param string $assetPath
     * @return null|string
     */
    public function getAssetUri(string $assetPath):?string;
}
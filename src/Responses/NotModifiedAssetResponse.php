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
// Date:     03/05/2018
// Time:     16:31
// Project:  AssetsMiddleware
//
declare(strict_types=1);
namespace CodeInc\AssetsMiddleware\Responses;
use CodeInc\AssetsMiddleware\Assets\AssetInterface;
use CodeInc\Psr7Responses\NotModifiedResponse;


/**
 * Class AssetNotModifiedResponse
 *
 * @package CodeInc\AssetsMiddleware\Responses
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class NotModifiedAssetResponse extends NotModifiedResponse implements AssetResponseInterface
{
    /**
     * @var AssetInterface
     */
    private $asset;

    /**
     * AssetNotModifiedResponse constructor.
     *
     * @param AssetInterface $asset
     */
    public function __construct(AssetInterface $asset)
    {
        $this->asset = $asset;
        parent::__construct();
    }

    /**
     * @inheritdoc
     * @return AssetInterface
     */
    public function getAsset():AssetInterface
    {
        return $this->asset;
    }
}
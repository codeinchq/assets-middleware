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
namespace CodeInc\AssetsMiddleware\Assets;
use GuzzleHttp\Psr7\Response;


/**
 * Class AssetNotModifiedResponse
 *
 * @package CodeInc\AssetsMiddleware\Assets
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class AssetNotModifiedResponse extends Response implements AssetResponseInterface
{
    /**
     * @var string
     */
    private $assetName;

    /**
     * AssetNotModifiedResponse constructor.
     *
     * @param string $assetName
     * @param int $status
     * @param array $headers
     * @param null $body
     * @param string $version
     * @param null|string $reason
     */
    public function __construct(string $assetName, int $status = 304, array $headers = [], $body = null,
        string $version = '1.1', ?string $reason = null)
    {
        $this->assetName = $assetName;
        parent::__construct($status, $headers, $body, $version, $reason);
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function getAssetName():string
    {
        return $this->assetName;
    }
}
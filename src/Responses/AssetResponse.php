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
// Time:     16:30
// Project:  AssetsMiddleware
//
declare(strict_types=1);
namespace CodeInc\AssetsMiddleware\Responses;
use CodeInc\Psr7Responses\FileResponse;


/**
 * Class AssetResponse
 *
 * @package CodeInc\AssetsMiddleware\Responses
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class AssetResponse extends FileResponse implements AssetResponseInterface
{
    /**
     * @var string
     */
    private $assetPath;

    /**
     * AssetResponse constructor.
     *
     * @param string $assetPath
     * @throws \CodeInc\MediaTypes\Exceptions\MediaTypesException
     */
    public function __construct(string $assetPath)
    {
        $this->assetPath = $assetPath;
        parent::__construct($assetPath, basename($assetPath), null, false);
    }


    /**
     * @inheritdoc
     * @return string
     */
    public function getAssetPath():string
    {
        return $this->assetPath;
    }
}
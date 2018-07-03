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
namespace CodeInc\AssetsMiddleware\Assets;
use CodeInc\MediaTypes\MediaTypes;
use CodeInc\Psr7Responses\StreamResponse;
use function GuzzleHttp\Psr7\stream_for;
use MatthiasMullie\Minify;


/**
 * Class AssetMinifiedResponse
 *
 * @uses Minify\CSS
 * @uses Minify\JS
 * @package CodeInc\AssetsMiddleware\Assets
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class AssetMinifiedResponse extends StreamResponse implements AssetResponseInterface
{
    /**
     * @var string
     */
    private $assetName;

    /**
     * AssetResponse constructor.
     *
     * @param string $filePath
     * @param string $assetName
     * @param null|string $fileName
     * @param null|string $mimeType
     * @param bool $asAttachment
     * @param int $status
     * @param array $headers
     * @param string $version
     * @param null|string $reason
     * @throws \CodeInc\MediaTypes\Exceptions\MediaTypesException
     */
    public function __construct(string $filePath, string $assetName, ?string $fileName = null, ?string $mimeType = null,
        bool $asAttachment = false, int $status = 200, array $headers = [],
        string $version = '1.1', ?string $reason = null)
    {
        $this->assetName = $assetName;

        if (!$fileName) {
            $fileName = basename($assetName);
        }
        if (!$mimeType) {
            $mimeType = MediaTypes::getFilenameMediaType($fileName);
        }

        switch ($mimeType) {
            case 'text/css':
                $stream = stream_for((new Minify\CSS($filePath))->minify());
                break;

            case 'text/javascript':
                $stream = stream_for((new Minify\JS($filePath))->minify());
                break;

            default:
                $stream = stream_for($filePath);
                break;
        }

        parent::__construct(
            $stream,
            $mimeType,
            null,
            $fileName,
            $asAttachment,
            $status,
            $headers,
            $version,
            $reason
        );
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
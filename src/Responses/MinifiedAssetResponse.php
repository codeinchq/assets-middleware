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
use enshrined\svgSanitize\Sanitizer;
use function GuzzleHttp\Psr7\stream_for;
use MatthiasMullie\Minify;
use Psr\Http\Message\StreamInterface;
use RuntimeException;


/**
 * Class MinifiedAssetResponse
 *
 * @package CodeInc\AssetsMiddleware\Responses
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class MinifiedAssetResponse extends FileResponse implements AssetResponseInterface
{
    /**
     * @var string
     */
    private $assetPath;

    /**
     * @var string
     */
    private $mediaType;

    /**
     * MinifiedAssetResponse constructor.
     *
     * @param string $assetPath
     * @param string $mediaType
     * @throws \CodeInc\MediaTypes\Exceptions\MediaTypesException
     */
    public function __construct(string $assetPath, string $mediaType)
    {
        $this->assetPath = $assetPath;
        $this->mediaType = $mediaType;
        parent::__construct(
            basename($assetPath),
            $this->buildStream($assetPath),
            200,
            '',
            $mediaType,
            null,
            false
        );
    }

    /**
     * @param string $filePath
     * @return StreamInterface
     */
    private function buildStream(string $filePath):StreamInterface
    {
        switch ($this->mediaType) {
            case 'text/css':
                $css = new Minify\CSS($filePath);
                $css->setImportExtensions([]);
                return stream_for($css->minify());

            case 'text/javascript':
            case 'application/javascript':
                return stream_for((new Minify\JS($filePath))->minify());

            case 'image/svg+xml':
                $svgContent = file_get_contents($filePath);
                if ($svgContent === false) {
                    throw new RuntimeException(
                        sprintf("Unable to read the SCF assets file '%s'", $filePath)
                    );
                }
                $sanitizer = new Sanitizer();
                $sanitizer->minify(true);
                return stream_for($sanitizer->sanitize($svgContent));

            default:
                $f = fopen($filePath, 'r');
                if ($f === false) {
                    throw new RuntimeException(
                        sprintf("Unable to open the assets file '%s'", $filePath)
                    );
                }
                return stream_for($f);
        }
    }

    /**
     * Returns the asset's path.
     *
     * @return string
     */
    public function getAssetPath():string
    {
        return $this->assetPath;
    }
}
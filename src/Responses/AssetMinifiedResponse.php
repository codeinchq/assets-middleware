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
use CodeInc\AssetsMiddleware\Assets\AssetInterface;
use CodeInc\Psr7Responses\FileResponse;
use enshrined\svgSanitize\Sanitizer;
use function GuzzleHttp\Psr7\stream_for;
use MatthiasMullie\Minify;
use Psr\Http\Message\StreamInterface;


/**
 * Class AssetMinifiedResponse
 *
 * @package CodeInc\AssetsMiddleware\Responses
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class AssetMinifiedResponse extends FileResponse implements AssetResponseInterface
{
    /**
     * @var AssetInterface
     */
    private $asset;

    /**
     * AssetMinifiedResponse constructor.
     *
     * @param AssetInterface $asset
     * @throws \CodeInc\MediaTypes\Exceptions\MediaTypesException
     */
    public function __construct(AssetInterface $asset)
    {
        $this->asset = $asset;
        parent::__construct(
            $asset->getFilename(),
            $this->getAssetMinifiedContent(),
            200,
            '',
            $asset->getMediaType(),
            $asset->getSize(),
            $asset->asAttachment()
        );
    }

    /**
     * @return StreamInterface
     */
    private function getAssetMinifiedContent():StreamInterface
    {
        switch ($this->asset->getMediaType()) {
            case 'text/css':
                $css = new Minify\CSS();
                $css->add($this->asset->getContent()->__toString());
                $css->setImportExtensions([]);
                return stream_for($css->minify());

            case 'text/javascript':
            case 'application/javascript':
                $js = new Minify\JS();
                $js->add($this->asset->getContent()->__toString());
                return stream_for($js->minify());

            case 'image/svg+xml':
                $sanitizer = new Sanitizer();
                $sanitizer->minify(true);
                return stream_for($sanitizer->sanitize($this->asset->getContent()->__toString()));
        }
        return $this->asset->getContent();
    }

    /**
     * @return AssetInterface
     */
    public function getAsset():AssetInterface
    {
        return $this->asset;
    }
}
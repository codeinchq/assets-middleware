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
namespace CodeInc\AssetsMiddleware\Assets;
use CodeInc\AssetsMiddleware\Exceptions\AssetReadException;
use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Message\StreamInterface;


/**
 * Class LocalAsset
 *
 * @package CodeInc\AssetsMiddleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class LocalAsset extends StreamAsset
{
    /**
     * @var string
     */
    private $path;

    /**
     * StreamAsset constructor.
     *
     * @param string $path
     * @param null|string $filename
     * @param bool $asAttachment
     * @param null|string $mediaType
     * @param int|null $size
     * @throws \CodeInc\MediaTypes\Exceptions\MediaTypesException
     * @throws AssetReadException
     */
    public function __construct(string $path, ?string $filename = null, bool $asAttachment = false,
        ?string $mediaType = null, ?int $size = null)
    {
        $this->path = $path;
        parent::__construct(
            $this->getPathStream($path),
            $filename ?? basename($path),
            $this->readMTime($path),
            $asAttachment,
            $mediaType,
            $size
        );
    }

    /**
     * Returns the local asset's path.
     *
     * @return string
     */
    public function getPath():string
    {
        return $this->path;
    }

    /**
     * Returns the last modification time for the given path.
     *
     * @param string $path
     * @return \DateTime|null
     */
    private function readMTime(string $path):?\DateTime
    {
        if (($mTime = filemtime($path)) !== false) {
            return new \DateTime('@'.$mTime);
        }
        return null;
    }

    /**
     * Returns the stream for the given path.
     *
     * @param string $path
     * @return StreamInterface
     * @throws AssetReadException
     */
    private function getPathStream(string $path):StreamInterface
    {
        if (($f = fopen($path, 'r')) === false) {
            throw new AssetReadException($path);
        }
        return stream_for($f);
    }
}
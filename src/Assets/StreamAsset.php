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
use CodeInc\MediaTypes\MediaTypes;
use Psr\Http\Message\StreamInterface;


/**
 * Class LocalAsset
 *
 * @package CodeInc\AssetsMiddleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class StreamAsset implements AssetInterface
{
    /**
     * @var null|string
     */
    private $filename;

    /**
     * @var int|null
     */
    private $size;

    /**
     * @var bool
     */
    private $asAttachment;

    /**
     * @var string
     */
    private $mediaType;

    /**
     * @var \GuzzleHttp\Psr7\Stream
     */
    private $content;

    /**
     * @var \DateTime
     */
    private $mTime;

    /**
     * StreamAsset constructor.
     *
     * @param StreamInterface $stream
     * @param null|string $filename
     * @param \DateTime|null $mTime
     * @param bool $asAttachment
     * @param null|string $mediaType
     * @param int|null $size
     * @throws \CodeInc\MediaTypes\Exceptions\MediaTypesException
     */
    public function __construct(StreamInterface $stream, string $filename, ?\DateTime $mTime = null,
        bool $asAttachment = false, ?string $mediaType = null, ?int $size = null)
    {
        $this->filename = $filename;
        $this->asAttachment = $asAttachment;
        $this->mTime = $mTime;
        $this->mediaType = $mediaType ?? MediaTypes::getFilenameMediaType($filename) ?? 'application/octet-stream';
        $this->size = $size ?? $stream->getSize();
        $this->content = $stream;
    }

    /**
     * Returns the asset's filename.
     *
     * @return string
     */
    public function getFilename():string
    {
        return $this->filename;
    }

    /**
     * Returns the asset's size or NULL if unknown.
     *
     * @return int|null
     */
    public function getSize():?int
    {
        return $this->size;
    }

    /**
     * Verifies if the assets must be downloaded as an attachment.
     *
     * @return bool
     */
    public function asAttachment():bool
    {
        return $this->asAttachment;
    }

    /**
     * Returns the assets media type or NULL if unknown.
     *
     * @return string
     */
    public function getMediaType():string
    {
        return $this->mediaType;
    }

    /**
     * Returns a stream to the assets interface.
     *
     * @return StreamInterface
     */
    public function getContent():StreamInterface
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     * @return \DateTime|null
     */
    public function getMTime():?\DateTime
    {
        return $this->mTime;
    }
}
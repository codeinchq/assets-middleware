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
use Psr\Http\Message\StreamInterface;


/**
 * Interface AssetInterface
 *
 * @package CodeInc\AssetsMiddleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
interface AssetInterface
{
    /**
     * Returns the asset's filename.
     *
     * @return string
     */
    public function getFilename():string;

    /**
     * Returns the asset's size or NULL if unknown.
     *
     * @return int|null
     */
    public function getSize():?int;

    /**
     * Returns the last modification time or NULL if unknown.
     *
     * @return \DateTime|null
     */
    public function getMTime():?\DateTime;

    /**
     * Verifies if the assets must be downloaded as an attachment.
     *
     * @return bool
     */
    public function asAttachment():bool;

    /**
     * Returns the assets media type or NULL if unknown.
     *
     * @return string
     */
    public function getMediaType():string;

    /**
     * Returns a stream to the assets interface.
     *
     * @return StreamInterface
     */
    public function getContent():StreamInterface;
}
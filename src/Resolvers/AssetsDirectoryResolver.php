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
use CodeInc\AssetsMiddleware\Assets\LocalAsset;
use CodeInc\AssetsMiddleware\Exceptions\NotADirectoryException;


/**
 * Class AssetsDirectoryResolver
 *
 * @package CodeInc\AssetsMiddleware\Resolvers
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class AssetsDirectoryResolver implements AssetResolverInterface
{
    /**
     * @var string
     */
    private $dirPath;

    /**
     * @var string
     */
    private $uriPrefix;

    /**
     * AssetsDirectoryResolver constructor.
     *
     * @param string $dirPath
     * @param string $uriPrefix
     * @throws NotADirectoryException
     */
    public function __construct(string $dirPath, string $uriPrefix)
    {
        if (($realDirPath = realpath($dirPath)) === false || !is_dir($realDirPath)) {
            throw new NotADirectoryException($dirPath);
        }
        $this->dirPath = $realDirPath;
        $this->uriPrefix = $uriPrefix;
    }

    /**
     * @return string
     */
    public function getDirPath():string
    {
        return $this->dirPath;
    }

    /**
     * @return string
     */
    public function getUriPrefix():string
    {
        return $this->uriPrefix;
    }

    /**
     * @inheritdoc
     * @param string $assetUri
     * @return AssetInterface|null
     * @throws \CodeInc\MediaTypes\Exceptions\MediaTypesException
     */
    public function getAsset(string $assetUri):?AssetInterface
    {
        if (preg_match('#^'.preg_quote($this->uriPrefix, '#').'(.+)#ui', $assetUri, $matches)) {
            $assetPath = $this->dirPath.DIRECTORY_SEPARATOR.$matches[1];
            if (($realAssetPath = realpath($assetPath)) !== false
                && substr($realAssetPath, 0, strlen($this->dirPath)) == $this->dirPath) {
                return new LocalAsset($realAssetPath);
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     * @param string $assetPath
     * @return null|string
     */
    public function getAssetUri(string $assetPath):?string
    {
        if (($realAssetPath = realpath($assetPath)) !== false
            && preg_match('#^'.preg_quote($this->dirPath, '#').'(.+)#ui', $realAssetPath, $matches)) {
            return $this->uriPrefix.$matches[1];
        }
        return null;
    }
}
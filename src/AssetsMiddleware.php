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
// Date:     05/03/2018
// Time:     17:15
// Project:  AssetsMiddleware
//
declare(strict_types = 1);
namespace CodeInc\AssetsMiddleware;
use CodeInc\AssetsMiddleware\Exceptions\InvalidAssetPathException;
use CodeInc\AssetsMiddleware\Exceptions\EmptyDirectoryKeyException;
use CodeInc\AssetsMiddleware\Exceptions\NotADirectoryException;
use CodeInc\AssetsMiddleware\Test\AssetsMiddlewareTest;


/**
 * Class AssetsMiddleware
 *
 * @package CodeInc\AssetsMiddleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 * @license MIT <https://github.com/CodeIncHQ/AssetsMiddleware/blob/master/LICENSE>
 * @link https://github.com/CodeIncHQ/AssetsMiddleware
 * @see AssetsMiddlewareTest
 * @version 2
 */
class AssetsMiddleware extends AbstractAssetsMiddleware
{
    /**
     * @var array
     */
    private $assetsDirectories = [];

    /**
     * Adds an assets directory
     *
     * @param string $directoryPath
     * @param string|null $directoryKey
     */
    public function addAssetsDirectory(string $directoryPath, string $directoryKey = null):void
    {
        if (!is_dir($directoryPath) || ($directoryPath = realpath($directoryPath)) === false) {
            throw new NotADirectoryException($directoryPath);
        }
        if ($directoryKey !== null && empty($directoryKey)) {
            throw new EmptyDirectoryKeyException($directoryPath);
        }
        $this->assetsDirectories[$directoryKey ?? md5($directoryPath)] = $directoryPath;
    }

    /**
     * @inheritdoc
     * @return iterable
     */
   protected function getAssetsDirectories():iterable
   {
       return $this->assetsDirectories;
   }

    /**
     * @inheritdoc
     * @param string $assetPath
     * @return null|string
     */
   public function getAssetUri(string $assetPath):?string
   {
       if (($realAssetPath = realpath($assetPath)) === false) {
           throw new InvalidAssetPathException($assetPath);
       }
       return parent::getAssetUri($realAssetPath);
   }
}
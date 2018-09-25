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
// Time:     16:15
// Project:  AssetsMiddleware
//
declare(strict_types=1);
namespace CodeInc\AssetsMiddleware;


/**
 * Class AssetsMiddlewareException
 *
 * @package CodeInc\AssetsMiddleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class AssetsMiddlewareException extends \Exception
{
    public const CODE_RESPONSE_ERROR = 1;
    public const CODE_NOT_A_DIRECTORY = 2;
    public const CODE_EMPTY_DIRECTORY_KEY = 3;

    /**
     * @param string $assetPath
     * @param \Throwable $error
     * @return AssetsMiddlewareException
     */
    public static function responseError(string $assetPath, \Throwable $error):self
    {
        return new self(sprintf("Error while building the PSR-7 response for the asset '%s'.", $assetPath),
            self::CODE_RESPONSE_ERROR, $error);
    }

    /**
     * @param string $directoryPath
     * @return AssetsMiddlewareException
     */
    public static function notADirectory(string $directoryPath):self
    {
        return new self(sprintf("The path '%s' is not a directory or does not exist.", $directoryPath),
            self::CODE_NOT_A_DIRECTORY);
    }

    /**
     * @param string $directoryPath
     * @return AssetsMiddlewareException
     */
    public static function emptyDirectoryKey(string $directoryPath):self
    {
        return new self(sprintf("The key of the directory '%s' can not empty.", $directoryPath),
            self::CODE_EMPTY_DIRECTORY_KEY);
    }
}
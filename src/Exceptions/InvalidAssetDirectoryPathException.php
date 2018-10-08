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
// Date:     28/09/2018
// Project:  AssetsMiddleware
//
declare(strict_types=1);
namespace CodeInc\AssetsMiddleware\Exceptions;
use Throwable;


/**
 * Class InvalidAssetDirectoryPathException
 *
 * @package CodeInc\AssetsMiddleware\Exceptions
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class InvalidAssetDirectoryPathException extends \RuntimeException implements AssetsMiddlewareException
{
    /**
     * @var string
     */
    private $dirPath;

    /**
     * InvalidAssetDirectoryPathException constructor.
     *
     * @param string $dirPath
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $dirPath, int $code = 0, Throwable $previous = null)
    {
        $this->dirPath = $dirPath;
        parent::__construct(
            sprintf("The assets directory path '%s' is not valid.", $dirPath),
            $code,
            $previous
        );
    }

    /**
     * @return string
     */
    public function getDirPath():string
    {
        return $this->dirPath;
    }
}
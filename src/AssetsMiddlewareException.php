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
use Throwable;


/**
 * Class AssetsMiddlewareException
 *
 * @package CodeInc\AssetsMiddleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 * @license MIT <https://github.com/CodeIncHQ/AssetsMiddleware/blob/master/LICENSE>
 * @link https://github.com/CodeIncHQ/AssetsMiddleware
 */
class AssetsMiddlewareException extends \Exception
{
    /**
     * @var AssetsMiddleware
     */
    private $assetsMiddleware;

    /**
     * AssetsMiddlewareException constructor.
     *
     * @param string $message
     * @param AssetsMiddleware $assetsMiddleware
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message, AssetsMiddleware $assetsMiddleware,
        int $code = 0, Throwable $previous = null)
    {
        $this->assetsMiddleware = $assetsMiddleware;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return AssetsMiddleware
     */
    public function getAssetsMiddleware():AssetsMiddleware
    {
        return $this->assetsMiddleware;
    }
}
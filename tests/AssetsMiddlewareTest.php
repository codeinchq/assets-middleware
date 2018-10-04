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
// Time:     16:34
// Project:  AssetsMiddleware
//
declare(strict_types=1);
namespace CodeInc\AssetsMiddleware\Test;
use CodeInc\AssetsMiddleware\Responses\AssetResponseInterface;
use CodeInc\AssetsMiddleware\Responses\NotModifiedAssetResponse;
use CodeInc\AssetsMiddleware\AssetsMiddleware;
use CodeInc\MiddlewareTestKit\FakeRequestHandler;
use CodeInc\MiddlewareTestKit\FakeServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;


/**
 * Class AssetsMiddlewareTest
 *
 * @uses AssetsMiddleware
 * @package CodeInc\AssetsMiddleware\Test
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
final class AssetsMiddlewareTest extends TestCase
{
    private const ASSETS = [
        __DIR__ . '/Assets/favicon.ico' => 'image/x-icon',
        __DIR__ . '/Assets/image.svg' => 'image/svg+xml',
        __DIR__ . '/Assets/lipsum.txt' => 'text/plain',
    ];

    /**
     * @throws \CodeInc\MediaTypes\Exceptions\MediaTypesException
     */
    public function testAssets():void
    {
        $middleware = new AssetsMiddleware(__DIR__ . '/Assets', true);
        $middleware->addAssetsDirectory('/assets/v2/');

        foreach (self::ASSETS as $path => $type) {
            self::assertFileExists($path);
            self::assertIsReadable($path);

            /** @var AssetResponseInterface $response */
            $response = $middleware->process(
                FakeServerRequest::getSecureServerRequestWithPath('/assets/v2/' . urlencode(basename($path))),
                new FakeRequestHandler()
            );

            self::assertInstanceOf(ResponseInterface::class, $response);
            self::assertInstanceOf(AssetResponseInterface::class, $response);
            self::assertEquals($type, $response->getHeaderLine('Content-Type'));
            self::assertNotEmpty($response->getHeaderLine('Cache-Control'));
            self::assertNotEmpty($response->getHeaderLine('ETag'));
            self::assertNotEmpty($response->getHeaderLine('Last-Modified'));
            self::assertEquals(filesize($path), $response->getHeaderLine('Content-Length'));
        }
    }

    /**
     */
    public function testUncachedAssets():void
    {
        $middleware = new AssetsMiddleware(__DIR__ . '/Assets', false);
        $middleware->registerAssetsDirectory('/assets/v2/');

        foreach (self::ASSETS as $path => $type) {
            self::assertFileExists($path);
            self::assertIsReadable($path);

            $response = $middleware->process(
                FakeServerRequest::getSecureServerRequestWithPath('/assets/v2/' . urlencode(basename($path))),
                new FakeRequestHandler()
            );

            self::assertInstanceOf(ResponseInterface::class, $response);
            self::assertInstanceOf(AssetResponseInterface::class, $response);
            self::assertEmpty($response->getHeaderLine('Cache-Control'));
            self::assertEmpty($response->getHeaderLine('ETag'));
            self::assertEmpty($response->getHeaderLine('Last-Modified'));
            self::assertEquals(filesize($path), $response->getHeaderLine('Content-Length'));
        }
    }

    /**
     */
    public function testNotFoundAsset():void
    {
        $middleware = new AssetsMiddleware(__DIR__ . '/Assets');
        $middleware->registerAssetsDirectory('/assets/v2/');
        $response = $middleware->process(
            FakeServerRequest::getSecureServerRequestWithPath('/assets/v2/a-not-found-asset.bin'),
            new FakeRequestHandler()
        );
        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertNotInstanceOf(AssetResponseInterface::class, $response);
    }

    /**
     */
    public function testNonAssetRequest():void
    {
        $middleware = new AssetsMiddleware(__DIR__ . '/Assets');
        $middleware->registerAssetsDirectory('/assets/v2/');
        $response = $middleware->process(
            FakeServerRequest::getSecureServerRequestWithPath('/a-page.html'),
            new FakeRequestHandler()
        );
        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertNotInstanceOf(AssetResponseInterface::class, $response);
    }

    /**
     */
    public function testDateCacheAsset():void
    {
        $middleware = new AssetsMiddleware(__DIR__ . '/Assets');
        $middleware->registerAssetsDirectory('/assets/');

        $request = FakeServerRequest::getSecureServerRequestWithPath('/assets/image.svg')
            ->withHeader('If-Modified-Since', date('D, d M Y H:i:s \G\M\T'));

        /** @var AssetResponseInterface $response */
        $response = $middleware->process($request, new FakeRequestHandler());

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertInstanceOf(NotModifiedAssetResponse::class, $response);
        self::assertEquals(basename($response->getAssetPath()), 'image.svg');
    }

    /**
     */
    public function testEtagCacheAsset():void
    {
        $middleware = new AssetsMiddleware(__DIR__ . '/Assets');
        $middleware->registerAssetsDirectory('/assets/');

        $request = FakeServerRequest::getSecureServerRequestWithPath('/assets/image.svg')
            ->withHeader('If-None-Match', '"9fda03907099301a7e94f69f6502b3f3805bf1c3"');

        /** @var AssetResponseInterface $response */
        $response = $middleware->process($request, new FakeRequestHandler());

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertInstanceOf(NotModifiedAssetResponse::class, $response);
        self::assertEquals(basename($response->getAssetPath()), 'image.svg');
    }
}
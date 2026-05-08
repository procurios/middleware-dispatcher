<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher\test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Procurios\Http\MiddlewareDispatcher\CallableBasedMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CallableBasedMiddlewareTest extends TestCase
{
    #[DataProvider('provideCallbacks')]
    public function testThatCallableIsSupported(callable $callable)
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createStub(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createStub(ResponseInterface::class);

        /** @var MockObject|RequestHandlerInterface $delegate */
        $delegate = $this->createMock(RequestHandlerInterface::class);
        $delegate
            ->expects($this->once())
            ->method('handle')
            ->willReturn($response)
        ;

        $middleware = new CallableBasedMiddleware($callable);
        $this->assertSame($response, $middleware->process($request, $delegate));
    }

    public function testThatContainsWillReturnTrueForIdenticalCallback()
    {
        $callback = function () {};
        $otherCallback = function () {};

        $middleware = new CallableBasedMiddleware($callback);

        $this->assertTrue($middleware->contains($callback));
        $this->assertFalse($middleware->contains($otherCallback));
    }

    public static function provideCallbacks(): array
    {
        return [
            'callable next and no type hints' => [
                function ($request, $next) {
                    return $next($request);
                }
            ],

            'callable next and type hints' => [
                function (ServerRequestInterface $request, callable $next) {
                    return $next($request);
                }
            ],

            'RequestHandlerInterface frame and no type hints' => [
                function ($request, $frame) {
                    /** @var RequestHandlerInterface $frame */
                    return $frame->handle($request);
                }
            ],

            'RequestHandlerInterface frame and type hints' => [
                function (ServerRequestInterface $request, RequestHandlerInterface $frame) {
                    return $frame->handle($request);
                }
            ],
        ];
    }
}

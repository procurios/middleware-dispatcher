<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher\test;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Procurios\Http\MiddlewareDispatcher\CallableBasedMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Middleware\DelegateInterface;

class CallableBasedMiddlewareTest extends TestCase
{
    /**
     * @dataProvider provideCallbacks
     * @param callable $callable
     */
    public function testThatCallableIsSupported(callable $callable)
    {
        /** @var RequestInterface $request */
        $request = $this->createMock(RequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        /** @var PHPUnit_Framework_MockObject_MockObject|DelegateInterface $delegate */
        $delegate = $this->createMock(DelegateInterface::class);
        $delegate
            ->expects($this->any())
            ->method('next')
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

    /**
     * @return array
     */
    public function provideCallbacks()
    {
        return [
            'callable next and no type hints' => [
                function ($request, $next) {
                    return $next($request);
                }
            ],

            'callable next and type hints' => [
                function (RequestInterface $request, callable $next) {
                    return $next($request);
                }
            ],

            'DelegateInterface frame and no type hints' => [
                function ($request, $frame) {
                    /** @var DelegateInterface $frame */
                    return $frame->next($request);
                }
            ],

            'DelegateInterface frame and type hints' => [
                function (RequestInterface $request, DelegateInterface $frame) {
                    return $frame->next($request);
                }
            ],
        ];
    }
}

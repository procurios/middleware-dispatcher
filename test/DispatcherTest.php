<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher\test;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Procurios\Http\MiddlewareDispatcher\CallableBasedMiddleware;
use Procurios\Http\MiddlewareDispatcher\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionObject;

class DispatcherTest extends TestCase
{
    public function testThatEveryMiddlewareIsCalledInTheRightOrder()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $expectedResponse */
        $expectedResponse = $this->createMock(ResponseInterface::class);
        /** @var RequestHandlerInterface $fallbackHandler */
        $fallbackHandler = $this->createMock(RequestHandlerInterface::class);

        $callLog = [];
        $dispatcher = (new Dispatcher($fallbackHandler))
            ->withMiddleware($this->createMiddleware('foo', $callLog, $request, $expectedResponse))
            ->withMiddleware($this->createMiddleware('bar', $callLog, $request, $expectedResponse))
            ->withCallback($this->createCallback('baz', $callLog, $request, $expectedResponse))
            ->withMiddleware($this->createMiddleware('qux', $callLog, $request, $expectedResponse, $expectedResponse))
        ;

        $this->assertSame($expectedResponse, $dispatcher->handle($request));
        $this->assertSame(['foo', 'bar', 'baz', 'qux'], $callLog);
    }

    public function testThatEveryMiddlewareDispatcherCanProcessMultipleTimes()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var RequestHandlerInterface $fallbackHandler */
        $fallbackHandler = $this->createMock(RequestHandlerInterface::class);

        $callLog = [];
        $dispatcher = (new Dispatcher($fallbackHandler))
            ->withMiddleware($this->createMiddleware('foo', $callLog, $request, $response))
            ->withCallback($this->createCallback('bar', $callLog, $request, $response))
            ->withMiddleware($this->createMiddleware('baz', $callLog, $request, $response))
            ->withMiddleware($this->createMiddleware('qux', $callLog, $request, $response, $response))
        ;

        $this->assertSame($response, $dispatcher->handle($request));
        $this->assertSame(['foo', 'bar', 'baz', 'qux'], $callLog);

        $this->assertSame($response, $dispatcher->handle($request));
        $this->assertSame(['foo', 'bar', 'baz', 'qux', 'foo', 'bar', 'baz', 'qux'], $callLog);
    }

    public function testThatMiddlewareCanBeRemoved()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var RequestHandlerInterface $fallbackHandler */
        $fallbackHandler = $this->createMock(RequestHandlerInterface::class);

        $callLog = [];
        $middlewareBar = $this->createMiddleware('bar', $callLog, $request, $response);
        $callbackBaz = $this->createCallback('baz', $callLog, $request, $response);
        $dispatcher = (new Dispatcher($fallbackHandler))
            ->withMiddleware($this->createMiddleware('foo', $callLog, $request, $response))
            ->withMiddleware($middlewareBar)
            ->withCallback($callbackBaz)
            ->withMiddleware($this->createMiddleware('qux', $callLog, $request, $response, $response))
        ;

        $dispatcher->handle($request);
        $this->assertSame(['foo', 'bar', 'baz', 'qux'], $callLog);

        $dispatcher2 = $dispatcher->withoutMiddleware($middlewareBar);

        $callLog = [];
        $dispatcher2->handle($request);
        $this->assertSame(['foo', 'baz', 'qux'], $callLog);

        $dispatcher3 = $dispatcher2->withoutMiddleware($middlewareBar);

        $callLog = [];
        $dispatcher3->handle($request);
        $this->assertSame(['foo', 'baz', 'qux'], $callLog);

        $dispatcher4 = $dispatcher3->withoutCallback($callbackBaz);

        $callLog = [];
        $dispatcher4->handle($request);
        $this->assertSame(['foo', 'qux'], $callLog);

        $dispatcher5 = $dispatcher4->withoutCallback($callbackBaz);

        $callLog = [];
        $dispatcher5->handle($request);
        $this->assertSame(['foo', 'qux'], $callLog);
    }

    public function testThatAnEmptyQueueCallsTheFallbackHandler()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var PHPUnit_Framework_MockObject_MockObject|RequestHandlerInterface $fallbackHandler */
        $fallbackHandler = $this->createMock(RequestHandlerInterface::class);

        $fallbackHandler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $dispatcher = new Dispatcher($fallbackHandler);
        $this->assertSame($response, $dispatcher->handle($request));
    }

    public function testThatADispatcherWithoutMiddlewareThatCreatesAResponseCallsTheFallbackHandler()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var PHPUnit_Framework_MockObject_MockObject|RequestHandlerInterface $fallbackHandler */
        $fallbackHandler = $this->createMock(RequestHandlerInterface::class);

        $fallbackHandler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response)
        ;

        $dispatcher = (new Dispatcher($fallbackHandler))
            ->withCallback(
                function ($request, $next) {
                    return $next($request);
                }
            );

        $this->assertSame($response, $dispatcher->handle($request));
    }

    public function testThatMiddlewareAfterResponseCreatingMiddlewareWillNotBeCalled()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var RequestHandlerInterface $fallbackHandler */
        $fallbackHandler = $this->createMock(RequestHandlerInterface::class);

        $callLog = [];
        $dispatcher = (new Dispatcher($fallbackHandler))
            ->withMiddleware($this->createMiddleware('foo', $callLog, $request, $response))
            ->withMiddleware($this->createMiddleware('qux', $callLog, $request, $response, $response))
            ->withMiddleware($this->createMiddleware('bar', $callLog, $request, $response))
            ->withMiddleware($this->createMiddleware('baz', $callLog, $request, $response))
        ;

        $dispatcher->handle($request);
        $this->assertSame(['foo', 'qux'], $callLog);
    }

    public function testThatMiddlewareDispatcherIsImmutable()
    {
        $foo = new CallableBasedMiddleware(function ($request, $next) {return $next($request);});
        $bar = new CallableBasedMiddleware(function ($request, $next) {return $next($request);});

        $baz = function () {return $this->createMock(ResponseInterface::class);};
        $qux = function () {};

        /** @var RequestHandlerInterface $fallbackHandler */
        $fallbackHandler = $this->createMock(RequestHandlerInterface::class);

        $dispatcher = (new Dispatcher($fallbackHandler))
            ->withMiddleware($foo)
            ->withCallback($baz)
        ;

        $currentState = $this->getCurrentState($dispatcher);

        $dispatcher->withMiddleware($bar);
        $dispatcher->withCallback($qux);
        $dispatcher->withoutMiddleware($foo);
        $dispatcher->withoutCallback($baz);
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $dispatcher->handle($request);

        $this->assertSame($currentState, $this->getCurrentState($dispatcher));
    }

    private function createMiddleware(
        string $identifier,
        array &$callLog,
        ServerRequestInterface $expectedRequest,
        ResponseInterface $expectedResponse,
        ResponseInterface $responseToReturn = null
    ): MiddlewareInterface {
        return new CallableBasedMiddleware(
            $this->createCallback($identifier, $callLog, $expectedRequest, $expectedResponse, $responseToReturn)
        );
    }

    private function createCallback(
        string $identifier,
        array &$callLog,
        ServerRequestInterface $expectedRequest,
        ResponseInterface $expectedResponse,
        ResponseInterface $responseToReturn = null
    ): callable {
        return function (ServerRequestInterface $request, RequestHandlerInterface $frame) use (
            $identifier,
            &$callLog,
            $expectedRequest,
            $expectedResponse,
            $responseToReturn
        ) {
            $callLog[] = $identifier;
            $this->assertSame($expectedRequest, $request);

            if (null !== $responseToReturn) {
                return $responseToReturn;
            }

            $response = $frame->handle($request);

            $this->assertSame($expectedResponse, $response);
            return $response;
        };
    }

    private function getCurrentState(Dispatcher $dispatcher): array
    {
        $state = [];
        $reflector = new ReflectionObject($dispatcher);
        foreach ($reflector->getProperties() as $propertyReflector) {
            $propertyReflector->setAccessible(true);
            $state[$propertyReflector->getName()] = $propertyReflector->getValue($dispatcher);
        }
        return $state;
    }
}

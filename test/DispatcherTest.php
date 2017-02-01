<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher\test;

use PHPUnit_Framework_TestCase;
use Procurios\Http\MiddlewareDispatcher\CallableBasedMiddleware;
use Procurios\Http\MiddlewareDispatcher\Dispatcher;
use Procurios\Http\MiddlewareDispatcher\QueueIsEmpty;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Middleware\DelegateInterface;
use Psr\Http\Middleware\MiddlewareInterface;
use ReflectionObject;

/**
 *
 */
class DispatcherTest extends PHPUnit_Framework_TestCase
{
    public function testThatEveryMiddlewareIsCalledInTheRightOrder()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        $callLog = [];
        $dispatcher = (new Dispatcher())
            ->withMiddleware($this->createMiddleware('foo', $callLog, $request, $response))
            ->withMiddleware($this->createMiddleware('bar', $callLog, $request, $response))
            ->withCallback($this->createCallback('baz', $callLog, $request, $response))
            ->withMiddleware($this->createMiddleware('qux', $callLog, $request, $response, $response))
        ;

        $dispatcher->process($request);
        $this->assertSame(['foo', 'bar', 'baz', 'qux'], $callLog);
    }

    public function testThatEveryMiddlewareDispatcherCanProcessMultipleTimes()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        $callLog = [];
        $dispatcher = (new Dispatcher())
            ->withMiddleware($this->createMiddleware('foo', $callLog, $request, $response))
            ->withCallback($this->createCallback('bar', $callLog, $request, $response))
            ->withMiddleware($this->createMiddleware('baz', $callLog, $request, $response))
            ->withMiddleware($this->createMiddleware('qux', $callLog, $request, $response, $response))
        ;

        $dispatcher->process($request);
        $this->assertSame(['foo', 'bar', 'baz', 'qux'], $callLog);

        $dispatcher->process($request);
        $this->assertSame(['foo', 'bar', 'baz', 'qux', 'foo', 'bar', 'baz', 'qux'], $callLog);
    }

    public function testThatMiddlewareCanBeRemoved()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        $callLog = [];
        $middlewareBar = $this->createMiddleware('bar', $callLog, $request, $response);
        $callbackBaz = $this->createCallback('baz', $callLog, $request, $response);
        $dispatcher = (new Dispatcher())
            ->withMiddleware($this->createMiddleware('foo', $callLog, $request, $response))
            ->withMiddleware($middlewareBar)
            ->withCallback($callbackBaz)
            ->withMiddleware($this->createMiddleware('qux', $callLog, $request, $response, $response))
        ;

        $dispatcher->process($request);
        $this->assertSame(['foo', 'bar', 'baz', 'qux'], $callLog);

        $dispatcher2 = $dispatcher->withoutMiddleware($middlewareBar);

        $callLog = [];
        $dispatcher2->process($request);
        $this->assertSame(['foo', 'baz', 'qux'], $callLog);

        $dispatcher3 = $dispatcher2->withoutMiddleware($middlewareBar);

        $callLog = [];
        $dispatcher3->process($request);
        $this->assertSame(['foo', 'baz', 'qux'], $callLog);

        $dispatcher4 = $dispatcher3->withoutCallback($callbackBaz);

        $callLog = [];
        $dispatcher4->process($request);
        $this->assertSame(['foo', 'qux'], $callLog);

        $dispatcher5 = $dispatcher4->withoutCallback($callbackBaz);

        $callLog = [];
        $dispatcher5->process($request);
        $this->assertSame(['foo', 'qux'], $callLog);
    }

    public function testThatAnEmptyQueueThrowsAnException()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);

        $dispatcher = new Dispatcher();

        $this->expectException(QueueIsEmpty::class);
        $dispatcher->process($request);
    }

    public function testThatADispatcherWithoutMiddlewareThatCreatesAResponseThrowsAnException()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);

        $dispatcher = (new Dispatcher())
            ->withCallback(
                function ($request, $next) {
                    return $next($request);
                }
            );

        $this->expectException(QueueIsEmpty::class);
        $dispatcher->process($request);
    }

    public function testThatMiddlewareAfterResponseCreatingMiddlewareWillNotBeCalled()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        $callLog = [];
        $dispatcher = (new Dispatcher())
            ->withMiddleware($this->createMiddleware('foo', $callLog, $request, $response))
            ->withMiddleware($this->createMiddleware('qux', $callLog, $request, $response, $response))
            ->withMiddleware($this->createMiddleware('bar', $callLog, $request, $response))
            ->withMiddleware($this->createMiddleware('baz', $callLog, $request, $response))
        ;

        $dispatcher->process($request);
        $this->assertSame(['foo', 'qux'], $callLog);
    }

    public function testThatMiddlewareDispatcherIsImmutable()
    {
        $foo = new CallableBasedMiddleware(function ($request, $next) {return $next($request);});
        $bar = new CallableBasedMiddleware(function ($request, $next) {return $next($request);});

        $baz = function () {return $this->createMock(ResponseInterface::class);};
        $qux = function () {};

        $dispatcher = (new Dispatcher())
            ->withMiddleware($foo)
            ->withCallback($baz)
        ;

        $currentState = $this->getCurrentState($dispatcher);

        $dispatcher->withMiddleware($bar);
        $dispatcher->withCallback($qux);
        $dispatcher->withoutMiddleware($foo);
        $dispatcher->withoutCallback($baz);
        /** @var RequestInterface $request */
        $request = $this->createMock(RequestInterface::class);
        $dispatcher->process($request);

        $this->assertSame($currentState, $this->getCurrentState($dispatcher));
    }

    /**
     * Create middleware with expectations
     * @param string $identifier
     * @param array $callLog
     * @param RequestInterface $expectedRequest
     * @param ResponseInterface $expectedResponse
     * @param ResponseInterface|null $responseToReturn
     * @return MiddlewareInterface
     */
    private function createMiddleware(
        $identifier,
        array &$callLog,
        RequestInterface $expectedRequest,
        ResponseInterface $expectedResponse,
        ResponseInterface $responseToReturn = null
    ) {
        return new CallableBasedMiddleware(
            $this->createCallback($identifier, $callLog, $expectedRequest, $expectedResponse, $responseToReturn)
        );
    }

    /**
     * @param string $identifier
     * @param array $callLog
     * @param RequestInterface $expectedRequest
     * @param ResponseInterface $expectedResponse
     * @param ResponseInterface $responseToReturn
     * @return \Closure
     */
    private function createCallback(
        $identifier,
        &$callLog,
        RequestInterface $expectedRequest,
        ResponseInterface $expectedResponse,
        ResponseInterface $responseToReturn = null
    ) {
        return function (RequestInterface $request, DelegateInterface $frame) use (
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

            $response = $frame->next($request);

            $this->assertSame($expectedResponse, $response);
            return $response;
        };
    }

    /**
     * @param Dispatcher $dispatcher
     * @return array
     */
    private function getCurrentState(Dispatcher $dispatcher)
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

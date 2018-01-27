<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher\test;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Procurios\Http\MiddlewareDispatcher\CallableRequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CallableRequestHandlerTest extends TestCase
{
    public function testThatCallableDelegatePassesNextCallToTargetDelegate()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        /** @var PHPUnit_Framework_MockObject_MockObject|RequestHandlerInterface $delegate */
        $delegate = $this->createMock(RequestHandlerInterface::class);
        $delegate
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response)
        ;

        $this->assertSame($response, (new CallableRequestHandler($delegate))->handle($request));
    }

    public function testThatCallableDelegatePassesDirectInvocationToTargetDelegate()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        /** @var PHPUnit_Framework_MockObject_MockObject|RequestHandlerInterface $delegate */
        $delegate = $this->createMock(RequestHandlerInterface::class);
        $delegate
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response)
        ;

        $this->assertSame($response, \call_user_func(new CallableRequestHandler($delegate), $request));
    }
}

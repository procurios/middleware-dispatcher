<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher\test;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Procurios\Http\MiddlewareDispatcher\CallableRequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CallableRequestHandlerTest extends TestCase
{
    public function testThatCallableDelegatePassesNextCallToTargetDelegate()
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
            ->with($request)
            ->willReturn($response)
        ;

        $this->assertSame($response, (new CallableRequestHandler($delegate))->handle($request));
    }

    public function testThatCallableDelegatePassesDirectInvocationToTargetDelegate()
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
            ->with($request)
            ->willReturn($response)
        ;

        $this->assertSame($response, (new CallableRequestHandler($delegate))($request));
    }
}

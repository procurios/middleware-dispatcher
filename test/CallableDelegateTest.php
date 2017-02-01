<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher\test;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Procurios\Http\MiddlewareDispatcher\CallableDelegate;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Middleware\DelegateInterface;

/**
 *
 */
class CallableDelegateTest extends PHPUnit_Framework_TestCase
{
    public function testThatCallableDelegatePassesNextCallToTargetDelegate()
    {
        /** @var RequestInterface $request */
        $request = $this->createMock(RequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        /** @var PHPUnit_Framework_MockObject_MockObject|DelegateInterface $delegate */
        $delegate = $this->createMock(DelegateInterface::class);
        $delegate
            ->expects($this->once())
            ->method('next')
            ->with($request)
            ->willReturn($response)
        ;

        $this->assertSame($response, (new CallableDelegate($delegate))->next($request));
    }

    public function testThatCallableDelegatePassesDirectInvocationToTargetDelegate()
    {
        /** @var RequestInterface $request */
        $request = $this->createMock(RequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        /** @var PHPUnit_Framework_MockObject_MockObject|DelegateInterface $delegate */
        $delegate = $this->createMock(DelegateInterface::class);
        $delegate
            ->expects($this->once())
            ->method('next')
            ->with($request)
            ->willReturn($response)
        ;

        $this->assertSame($response, call_user_func(new CallableDelegate($delegate), $request));
    }
}

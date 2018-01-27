<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher\test;

use PHPUnit\Framework\TestCase;
use Procurios\Http\MiddlewareDispatcher\CallableBasedDelegate;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CallableBasedDelegateTest extends TestCase
{
    public function testThatCallableBasedDelegateCallsCallable()
    {
        /** @var RequestInterface $expectedRequest */
        $expectedRequest = $this->createMock(RequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        $isCalled = false;
        $callback = function (RequestInterface $request) use ($response, $expectedRequest, &$isCalled) {
            $isCalled = true;
            $this->assertSame($expectedRequest, $request);
            return $response;
        };

        $delegate = new CallableBasedDelegate($callback);
        $this->assertSame($response, $delegate->next($expectedRequest));
        $this->assertTrue($isCalled);
    }
}

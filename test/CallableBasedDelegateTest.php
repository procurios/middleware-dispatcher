<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher\test;

use PHPUnit\Framework\TestCase;
use Procurios\Http\MiddlewareDispatcher\CallableBasedDelegate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CallableBasedDelegateTest extends TestCase
{
    public function testThatCallableBasedDelegateCallsCallable()
    {
        /** @var ServerRequestInterface $expectedRequest */
        $expectedRequest = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        $isCalled = false;
        $callback = function (ServerRequestInterface $request) use ($response, $expectedRequest, &$isCalled) {
            $isCalled = true;
            $this->assertSame($expectedRequest, $request);
            return $response;
        };

        $delegate = new CallableBasedDelegate($callback);
        $this->assertSame($response, $delegate->handle($expectedRequest));
        $this->assertTrue($isCalled);
    }
}

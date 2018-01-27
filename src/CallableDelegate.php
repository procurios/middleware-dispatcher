<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Delegate that can be used as a callable
 */
class CallableDelegate implements RequestHandlerInterface
{
    /** @var RequestHandlerInterface */
    private $delegate;

    public function __construct(RequestHandlerInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->delegate->handle($request);
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->delegate->handle($request);
    }
}

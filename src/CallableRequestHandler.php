<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * RequestHandler that can be used as a callable to comply with older middleware implementations
 */
class CallableRequestHandler implements RequestHandlerInterface
{
    /** @var RequestHandlerInterface */
    private $handler;

    public function __construct(RequestHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handler->handle($request);
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handler->handle($request);
    }
}

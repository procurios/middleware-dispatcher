<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Dispatcher implements RequestHandlerInterface
{
    /** @var MiddlewareInterface[] */
    private $queue = [];

    /** @var RequestHandlerInterface */
    private $fallbackHandler;

    public function __construct(RequestHandlerInterface $fallbackHandler)
    {
        $this->fallbackHandler = $fallbackHandler;
    }

    public function withMiddleware(MiddlewareInterface $middleware): self
    {
        $clone = clone $this;
        $clone->queue[] = $middleware;
        return $clone;
    }

    public function withoutMiddleware(MiddlewareInterface $middleware): self
    {
        $position = array_search($middleware, $this->queue, true);
        if ($position === false) {
            return $this;
        }

        $clone = clone $this;
        array_splice($clone->queue, $position, 1);
        return $clone;
    }

    public function withCallback(callable $callback): self
    {
        return $this->withMiddleware(new CallableBasedMiddleware($callback));
    }

    public function withoutCallback(callable $callback): self
    {
        foreach ($this->queue as $position => $middleware) {
            if ($middleware instanceof CallableBasedMiddleware && $middleware->contains($callback)) {
                return $this->withoutMiddleware($middleware);
            }
        }

        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queue = $this->queue;
        $handler = new CallableBasedRequestHandler(function (ServerRequestInterface $request) use (&$queue, &$handler) {
            $middleware = array_shift($queue);
            if (null === $middleware) {
                return $this->fallbackHandler->handle($request);
            }

            return $middleware->process($request, $handler);
        });

        return $handler->handle($request);
    }
}

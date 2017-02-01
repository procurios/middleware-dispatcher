<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Middleware\ClientMiddlewareInterface;
use Psr\Http\Middleware\MiddlewareInterface;
use Psr\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Middleware\StackInterface;

/**
 *
 */
final class Dispatcher implements StackInterface
{
    /** @var ServerMiddlewareInterface[]|ClientMiddlewareInterface[] */
    private $queue = [];

    /**
     * @inheritdoc
     * @return static
     */
    public function withMiddleware(MiddlewareInterface $middleware)
    {
        $clone = clone $this;
        $clone->queue[] = $middleware;
        return $clone;
    }

    /**
     * @inheritdoc
     * @return static
     */
    public function withoutMiddleware(MiddlewareInterface $middleware)
    {
        $position = array_search($middleware, $this->queue, true);
        if ($position === false) {
            return $this;
        }

        $clone = clone $this;
        array_splice($clone->queue, $position, 1);
        return $clone;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function withCallback(callable $callback)
    {
        return $this->withMiddleware(new CallableBasedMiddleware($callback));
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function withoutCallback(callable $callback)
    {
        foreach ($this->queue as $position => $middleware) {
            if ($middleware instanceof CallableBasedMiddleware && $middleware->contains($callback)) {
                return $this->withoutMiddleware($middleware);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     * @throws QueueIsEmpty
     */
    public function process(RequestInterface $request)
    {
        $queue = $this->queue;
        $frame = new CallableBasedDelegate(function (RequestInterface $request) use (&$queue, &$frame) {
            $middleware = array_shift($queue);
            if (null === $middleware) {
                throw new QueueIsEmpty();
            }

            return $middleware->process($request, $frame);
        });

        return $frame->next($request);
    }
}

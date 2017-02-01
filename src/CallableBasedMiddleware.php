<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Middleware\DelegateInterface;
use Psr\Http\Middleware\MiddlewareInterface;

/**
 * Middleware based on a callable accepting a RequestInterface and either a callable or DelegateInterface
 */
class CallableBasedMiddleware implements MiddlewareInterface
{
    /** @var callable */
    private $callback;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Process a server request and return a response.
     *
     * Takes the incoming request and optionally modifies it before delegating
     * to the next frame to get a response.
     *
     * @param RequestInterface $request
     * @param DelegateInterface $frame
     *
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, DelegateInterface $frame)
    {
        return call_user_func(
            $this->callback,
            $request,
            new CallableDelegate($frame)
        );
    }

    /**
     * @param callable $callback
     * @return bool
     */
    public function contains(callable $callback)
    {
        return $this->callback === $callback;
    }
}

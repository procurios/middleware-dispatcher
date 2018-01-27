<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware based on a callable accepting a ServerRequestInterface and either a callable or CallableDelegate
 */
class CallableBasedMiddleware implements MiddlewareInterface
{
    /** @var callable */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return \call_user_func(
            $this->callback,
            $request,
            new CallableDelegate($handler)
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

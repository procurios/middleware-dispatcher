<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Middleware\DelegateInterface;

class CallableBasedDelegate implements DelegateInterface
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
     * @param RequestInterface $request
     * @return mixed
     */
    public function next(RequestInterface $request)
    {
        return call_user_func($this->callback, $request);
    }
}

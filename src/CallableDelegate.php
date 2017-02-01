<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Middleware\DelegateInterface;

/**
 * Delegate that can be used as a callable
 */
class CallableDelegate implements DelegateInterface
{
    /** @var DelegateInterface */
    private $delegate;

    /**
     * @param DelegateInterface $delegate
     */
    public function __construct(DelegateInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function next(RequestInterface $request)
    {
        return $this->delegate->next($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request)
    {
        return $this->delegate->next($request);
    }
}

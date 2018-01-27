# Middleware Dispatcher
[![Build Status](https://travis-ci.org/procurios/middleware-dispatcher.svg?branch=master)](https://travis-ci.org/procurios/middleware-dispatcher)
[![Coverage Status](https://coveralls.io/repos/github/procurios/middleware-dispatcher/badge.svg?branch=master)](https://coveralls.io/github/procurios/middleware-dispatcher?branch=master)

Simple PSR-15 compliant middleware dispatcher

## Goal
The goal of this library is to provide a minimal implementation of the PSR-15 specification that is compatible with older callback middleware.

## Installation
```
composer require procurios/middleware-dispatcher
```

## Usage
See [PSR-15](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-15-request-handlers.md) for detailed information about middleware dispatchers.

```php
use Procurios\Http\MiddlewareDispatcher\Dispatcher;

$dispatcher = (new Dispatcher($myFallbackHandler))
    ->withMiddleware($myMiddleware)
    ->withMiddleware($myApp)
;

$response = $dispatcher->handle($request);
```

Or add anonymous callback middleware:

```php
use Procurios\Http\MiddlewareDispatcher\Dispatcher;

$dispatcher = (new Dispatcher($myFallbackHandler))
    ->withMiddleware($myMiddleware)
    ->withCallback(function (ServerRequestInterface $request, callable $next) {
        // noop
        return $next($request);
    })
    ->withCallback(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        // noop
        return $handler->handle($request);
    })
    ->withMiddleware($myApp)
;

$response = $dispatcher->handle($request);
```

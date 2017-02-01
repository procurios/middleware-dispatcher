# Middleware Dispatcher
[![Build Status](https://travis-ci.org/procurios/middleware-dispatcher.svg?branch=master)](https://travis-ci.org/procurios/middleware-dispatcher)
[![Coverage Status](https://coveralls.io/repos/github/procurios/middleware-dispatcher/badge.svg?branch=master)](https://coveralls.io/github/procurios/middleware-dispatcher?branch=master)

Simple PSR-15 compliant middleware dispatcher

## Goal
The goal of this library is to provide a minimal implementation of the PSR-15 specification (currently in draft) that is compatible with older callback middleware.

## Installation
```
composer require procurios/middleware-dispatcher
```

## Usage
See [PSR-15](https://github.com/php-fig/fig-standards/blob/master/proposed/http-middleware/middleware.md) for detailed information about middleware dispatchers.

```php
use Procurios\Http\MiddlewareDispatcher\Dispatcher;

$dispatcher = (new Dispatcher())
    ->withMiddleware($myMiddleware)
    ->withMiddleware($myApp)
;

$response = $dispatcher->process($request);
```

Or add anonymous callback middleware:

```php
use Procurios\Http\MiddlewareDispatcher\Dispatcher;

$dispatcher = (new Dispatcher())
    ->withMiddleware($myMiddleware)
    ->withCallback(function (RequestInterface $request, callable $next) {
        // noop
        return $next($request);
    })
    ->withMiddleware($myApp)
;

$response = $dispatcher->process($request);
```

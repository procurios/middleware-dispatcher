<?php
/**
 * © 2017 Procurios
 */
namespace Procurios\Http\MiddlewareDispatcher;

use RuntimeException;

/**
 * Thrown when the queue is empty during a next call
 */
class QueueIsEmpty extends RuntimeException
{
}

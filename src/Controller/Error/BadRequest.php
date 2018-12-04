<?php
declare(strict_types=1);

namespace AuthService\Controller\Error;

use Throwable;

class BadRequest extends ControllerError
{
    public function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, 400, $previous);
    }
}
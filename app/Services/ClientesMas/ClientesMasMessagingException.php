<?php

namespace App\Services\ClientesMas;

use RuntimeException;
use Throwable;

class ClientesMasMessagingException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $status = null,
        public readonly array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}

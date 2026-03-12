<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ImmutableRecordException extends Exception
{
    public function __construct(string $message = '', int $code = 403, ?Throwable $previous = null)
    {
        if ($message === '' || $message === '0') {
            $message = __('errors.immutable_record') !== 'errors.immutable_record'
                ? __('errors.immutable_record')
                : 'This financial record is immutable and cannot be modified or deleted.';
        }

        parent::__construct($message, $code, $previous);
    }
}

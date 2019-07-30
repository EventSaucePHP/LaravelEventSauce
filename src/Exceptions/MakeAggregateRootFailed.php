<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce\Exceptions;

use Exception;

final class MakeAggregateRootFailed extends Exception
{
    public static function fileExists(string $path): self
    {
        return new static("The file at path `{$path}` already exists.");
    }
}

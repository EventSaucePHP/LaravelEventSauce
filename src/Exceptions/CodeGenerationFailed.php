<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce\Exceptions;

use Exception;

final class CodeGenerationFailed extends Exception
{
    public static function definitionFileDoesNotExist(string $definitionFile): self
    {
        return new static("The code generation definition file specified in the config file (`{$definitionFile}`) does not exist.");
    }

    public static function codeGenerationNotInstalled(): self
    {
        return new static('Code generation is not installed. Please run composer require --dev eventsauce/code-generation:^1.0');
    }
}

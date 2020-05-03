<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce\Console;

use EventSauce\LaravelEventSauce\Exceptions\MakeFileFailed;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

abstract class MakeCommand extends Command
{
    protected Filesystem $filesystem;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->filesystem = $files;
    }

    protected function formatClassName(string $namespace): string
    {
        $name = ltrim($namespace, '\\/');
        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->laravel->getNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        return $this->formatClassName(trim($rootNamespace, '\\') . '\\' . $name);
    }

    protected function getPath(string $name): string
    {
        $name = Str::replaceFirst($this->laravel->getNamespace(), '', $name);

        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . '.php';
    }

    protected function ensureValidPaths(array $paths): void
    {
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $this->error("The file at path `{$path}` already exists.");

                throw MakeFileFailed::fileExists($path);
            }
        }
    }

    protected function makeDirectory(string $path): void
    {
        if (! $this->filesystem->isDirectory(dirname($path))) {
            $this->filesystem->makeDirectory(dirname($path), 0755, true, true);
        }
    }

    protected function getStubContent(string $stubName, array $replacements): string
    {
        $content = $this->filesystem->get(__DIR__ . "/stubs/{$stubName}.php.stub");

        foreach ($replacements as $search => $replace) {
            $content = str_replace("{{ {$search} }}", $replace, $content);
        }

        return $content;
    }

    protected function makeFiles(array $paths, array $replacements): void
    {
        collect($paths)->map(function (string $path, string $stubName) use ($replacements) {
            $this->filesystem->put($path, $this->getStubContent($stubName, $replacements));
        });
    }
}

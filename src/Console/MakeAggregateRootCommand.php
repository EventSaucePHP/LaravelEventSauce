<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce\Console;

use DateTimeImmutable;
use EventSauce\LaravelEventSauce\Exceptions\MakeAggregateRootFailed;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Spatie\LaravelEventSauce\Exceptions\CouldNotMakeAggregateRoot;

final class MakeAggregateRootCommand extends Command
{
    protected $signature = 'make:aggregate-root {class}';

    protected $description = 'Create a new aggregate root and resources';

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $filesystem;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->filesystem = $files;
    }

    public function handle(): void
    {
        $aggregateRootClass = $this->formatClassName($this->argument('class'));
        $aggregateRootPath = $this->getPath($aggregateRootClass);

        $aggregateRootIdClass = $this->formatClassName($this->argument('class').'Id');
        $aggregateRootIdPath = $this->getPath($aggregateRootIdClass);

        $aggregateRootRepositoryClass = $this->formatClassName($this->argument('class').'Repository');
        $aggregateRootRepositoryPath = $this->getPath($aggregateRootRepositoryClass);

        $this->ensureValidPaths([
            $aggregateRootPath,
            $aggregateRootIdPath,
            $aggregateRootRepositoryPath,
        ]);

        $this->makeDirectory($aggregateRootPath);

        $replacements = [
            'aggregateRoot' => class_basename($aggregateRootClass),
            'namespace' => substr($aggregateRootClass, 0, strrpos($aggregateRootClass, '\\')),
            'table' => Str::snake(class_basename($aggregateRootClass)).'_domain_messages',
            'migration' => 'Create'.ucfirst(class_basename($aggregateRootClass)).'DomainMessagesTable',
        ];

        collect([
            'AggregateRoot' => $aggregateRootPath,
            'AggregateRootId' => $aggregateRootIdPath,
            'AggregateRootRepository' => $aggregateRootRepositoryPath,
        ])->map(function (string $path, string $stubName) use ($replacements) {
            $this->filesystem->put($path, $this->getStubContent($stubName, $replacements));
        });

        $this->createMigration($replacements);

        $this->info('Aggregate root classes and migration created successfully!');
        $this->comment("Run `php artisan migrate` to create the {$replacements['table']} table.");
    }

    private function formatClassName(string $name): string
    {
        $name = ltrim($name, '\\/');
        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->laravel->getNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        return $this->formatClassName(trim($rootNamespace, '\\').'\\'.$name);
    }

    private function getPath(string $name): string
    {
        $name = Str::replaceFirst($this->laravel->getNamespace(), '', $name);

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }

    private function ensureValidPaths(array $paths): void
    {
        foreach ($paths as $path) {
            if (file_exists($path)) {
                throw MakeAggregateRootFailed::fileExists($path);
            }
        }
    }

    private function makeDirectory(string $path): void
    {
        if (! $this->filesystem->isDirectory(dirname($path))) {
            $this->filesystem->makeDirectory(dirname($path), 0755, true, true);
        }
    }

    private function getStubContent(string $stubName, array $replacements): string
    {
        $content = $this->filesystem->get(__DIR__."/stubs/{$stubName}.php.stub");

        foreach ($replacements as $search => $replace) {
            $content = str_replace("{{ {$search} }}", $replace, $content);
        }

        return $content;
    }

    private function createMigration(array $replacements): void
    {
        $timestamp = (new DateTimeImmutable())->format('Y_m_d_His');
        $filename = "{$timestamp}_create_{$replacements['table']}_table.php";

        $this->filesystem->put(
            $this->laravel->databasePath("migrations/{$filename}"),
            $this->getStubContent('create_domain_messages_table', $replacements)
        );
    }
}

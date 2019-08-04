<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Filesystem\Filesystem;

class MakeAggregateRootCommandTest extends TestCase
{
    /** @test */
    public function it_can_generate_aggregate_root_classes_and_a_migration()
    {
        $domainDirectory = $this->app->basePath('app/Domain');

        $timestamp = now()->format('Y_m_d_His');
        $migrationFileName = "{$timestamp}_create_registration_domain_messages_table.php";
        $migrationFile = $this->app->databasePath("migrations/$migrationFileName");

        $filesystem = $this->filesystem();

        if ($filesystem->exists($domainDirectory)) {
            $filesystem->deleteDirectory($domainDirectory);
        }

        if ($filesystem->exists($migrationFile)) {
            $filesystem->delete($migrationFile);
        }

        $this->artisan('make:aggregate-root', ['class' => 'Domain/Registration']);

        $this->assertFileExists($domainDirectory. '/Registration.php');
        $this->assertFileExists($domainDirectory. '/RegistrationId.php');
        $this->assertFileExists($domainDirectory. '/RegistrationRepository.php');
        $this->assertFileExists($migrationFile);

        $filesystem->delete($migrationFile);
    }

    private function filesystem(): Filesystem
    {
        return $this->app->make(Filesystem::class);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Console;

use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class MakeConsumerCommandTest extends TestCase
{
    /** @test */
    public function it_can_generate_a_consumer()
    {
        $domainDirectory = $this->app->basePath('app/Domain');

        $this->filesystem()->deleteDirectory($domainDirectory);

        $this->artisan('make:consumer', ['class' => 'Domain/SendEmailConfirmation']);

        $this->assertFileExists($domainDirectory.'/SendEmailConfirmation.php');
    }

    private function filesystem(): Filesystem
    {
        return $this->app->make(Filesystem::class);
    }
}

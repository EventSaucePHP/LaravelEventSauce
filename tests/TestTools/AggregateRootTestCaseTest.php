<?php

namespace Tests\TestTools;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\LaravelEventSauce\TestTools\AggregateRootTestCase;
use Illuminate\Support\Str;

class AggregateRootTestCaseTest extends AggregateRootTestCase
{
    protected function newAggregateRootId(): AggregateRootId
    {
        return SignupId::create();
    }

    protected function aggregateRootClassName(): string
    {
        return SignupProcess::class;
    }

    /** @test */
    public function itInitializes()
    {
        $this->assertTrue(true);
    }
}

class SignupId implements AggregateRootId
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public static function create()
    {
        return new static(Str::uuid());
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->id;
    }

    /**
     * @param string $aggregateRootId
     *
     * @return static
     */
    public static function fromString(string $aggregateRootId): AggregateRootId
    {
        return new static($aggregateRootId);
    }
}

class SignupProcess
{
    //
}
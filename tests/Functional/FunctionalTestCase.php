<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Kernel;
use PHPUnit\Framework\TestCase;

abstract class FunctionalTestCase extends TestCase
{
    protected Kernel $kernel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel = new Kernel('test', false);
        $this->kernel->boot();
    }

    protected function tearDown(): void
    {
        $this->kernel->shutdown();

        parent::tearDown();
    }
}

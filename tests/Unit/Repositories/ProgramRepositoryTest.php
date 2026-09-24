<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\ProgramRepository;
use App\Cycle;

class ProgramRepositoryTest extends TestCase
{

    protected ProgramRepository $programRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->programRepository = new ProgramRepository();
    }

    /** @test */
    public function it_can_find_program_by_id()
    {
        $cycle = Cycle::factory()->create();

        $result = $this->programRepository->findById($cycle->id);

        $this->assertInstanceOf(Cycle::class, $result);
        $this->assertEquals($cycle->id, $result->id);
    }

    /** @test */
    public function it_returns_null_when_program_not_found()
    {
        $result = $this->programRepository->findById(99999);

        $this->assertNull($result);
    }
}


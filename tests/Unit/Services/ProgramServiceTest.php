<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ProgramService;
use App\Repositories\ProgramRepository;
use App\Cycle;
use App\User;
use App\Company;
use Illuminate\Support\Facades\DB;
use Mockery;

class ProgramServiceTest extends TestCase
{

    protected ProgramService $programService;
    protected $programRepoMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->programRepoMock = Mockery::mock(ProgramRepository::class);
        $this->programService = new ProgramService($this->programRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_get_program_mentors()
    {
        $program = Cycle::factory()->create();
        $mentors = User::factory()->count(5)->create();
        
        foreach ($mentors as $mentor) {
            DB::table('cycles_mentors')->insert([
                'cycle_id' => $program->id,
                'user_id' => $mentor->id,
            ]);
        }

        $this->programRepoMock
            ->shouldReceive('findById')
            ->once()
            ->with($program->id)
            ->andReturn($program);

        $result = $this->programService->getProgramMentors($program->id);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertGreaterThan(0, $result->count());
    }

    /** @test */
    public function it_returns_not_found_when_program_does_not_exist()
    {
        $this->programRepoMock
            ->shouldReceive('findById')
            ->once()
            ->with(99999)
            ->andReturn(null);

        $result = $this->programService->getProgramMentors(99999);

        // Service returns error response using ApiResponder trait
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
    }

    /** @test */
    public function it_can_search_program_mentors()
    {
        $program = Cycle::factory()->create();
        $mentor = User::factory()->create([
            'first_name_en' => 'John',
            'last_name_en' => 'Mentor',
        ]);
        
        DB::table('cycles_mentors')->insert([
            'cycle_id' => $program->id,
            'user_id' => $mentor->id,
        ]);

        $this->programRepoMock
            ->shouldReceive('findById')
            ->once()
            ->with($program->id)
            ->andReturn($program);

        $result = $this->programService->getProgramMentors($program->id, 'John');

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
    }

    /** @test */
    public function it_can_get_program_startups()
    {
        $program = Cycle::factory()->create();
        $companies = Company::factory()->count(10)->create(['cycle' => $program->id]);

        $this->programRepoMock
            ->shouldReceive('findById')
            ->once()
            ->with($program->id)
            ->andReturn($program);

        $result = $this->programService->getProgramStartups($program->id);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertGreaterThan(0, $result->count());
    }

    /** @test */
    public function it_can_search_program_startups()
    {
        $program = Cycle::factory()->create();
        Company::factory()->create([
            'cycle' => $program->id,
            'name_en' => 'Test Startup',
        ]);
        Company::factory()->create([
            'cycle' => $program->id,
            'name_en' => 'Another Company',
        ]);

        $this->programRepoMock
            ->shouldReceive('findById')
            ->once()
            ->with($program->id)
            ->andReturn($program);

        $result = $this->programService->getProgramStartups($program->id, 'Test');

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
    }

    /** @test */
    public function it_can_paginate_program_startups()
    {
        $program = Cycle::factory()->create();
        Company::factory()->count(25)->create(['cycle' => $program->id]);

        $this->programRepoMock
            ->shouldReceive('findById')
            ->once()
            ->with($program->id)
            ->andReturn($program);

        $result = $this->programService->getProgramStartups($program->id, null, 1, 10);

        $this->assertEquals(10, $result->count());
        $this->assertEquals(25, $result->total());
    }

    /** @test */
    public function it_can_sort_program_startups()
    {
        $program = Cycle::factory()->create();
        Company::factory()->create(['cycle' => $program->id, 'name_en' => 'A Company']);
        Company::factory()->create(['cycle' => $program->id, 'name_en' => 'Z Company']);

        $this->programRepoMock
            ->shouldReceive('findById')
            ->once()
            ->with($program->id)
            ->andReturn($program);

        $result = $this->programService->getProgramStartups($program->id, null, 1, 10, 'name_en', 'asc');

        $this->assertEquals('A Company', $result->first()->name_en);
    }
}


<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Training;
use App\User;
use App\TrainingContent;
use App\TrainingCompleted;
use App\Expertise;
use App\Tags;
use Illuminate\Support\Facades\DB;

class TrainingModelTest extends TestCase
{

    /** @test */
    public function it_has_fillable_attributes()
    {
        $training = new Training();
        $fillable = $training->getFillable();

        $this->assertContains('organization_id', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('title', $fillable);
        $this->assertContains('dsc', $fillable);
    }

    /** @test */
    public function it_can_belong_to_user()
    {
        $user = User::factory()->create();
        $training = Training::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $training->user);
        $this->assertEquals($user->id, $training->user->id);
    }

    /** @test */
    public function it_can_have_contents()
    {
        $training = Training::factory()->create();
        TrainingContent::factory()->count(5)->create(['training_id' => $training->id]);

        $this->assertCount(5, $training->contents);
    }

    /** @test */
    public function it_can_have_completed_records()
    {
        $training = Training::factory()->create();
        TrainingCompleted::factory()->count(10)->create(['course_id' => $training->id]);

        $this->assertCount(10, $training->done);
    }

    /** @test */
    public function it_can_have_categories()
    {
        $training = Training::factory()->create();
        $category1 = Expertise::factory()->create();
        $category2 = Expertise::factory()->create();

        DB::table('categories_training')->insert([
            ['training_id' => $training->id, 'categories_id' => $category1->id],
            ['training_id' => $training->id, 'categories_id' => $category2->id],
        ]);

        $this->assertCount(2, $training->categories);
    }

    /** @test */
    public function it_can_have_tags()
    {
        $training = Training::factory()->create();
        $tag1 = Tags::factory()->create();
        $tag2 = Tags::factory()->create();

        DB::table('tag_training')->insert([
            ['training_id' => $training->id, 'tag_id' => $tag1->id],
            ['training_id' => $training->id, 'tag_id' => $tag2->id],
        ]);

        $this->assertCount(2, $training->tags);
    }

    /** @test */
    public function it_can_calculate_progress()
    {
        $user = User::factory()->create();
        $training = Training::factory()->create();
        $content1 = TrainingContent::factory()->create(['training_id' => $training->id]);
        $content2 = TrainingContent::factory()->create(['training_id' => $training->id]);
        $content3 = TrainingContent::factory()->create(['training_id' => $training->id]);

        TrainingCompleted::factory()->create([
            'course_id' => $training->id,
            'user_id' => $user->id,
            'training_id' => $content1->id,
        ]);
        TrainingCompleted::factory()->create([
            'course_id' => $training->id,
            'user_id' => $user->id,
            'training_id' => $content2->id,
        ]);

        $this->actingAs($user);
        $progress = $training->progress();

        $this->assertIsArray($progress);
        $this->assertArrayHasKey('completed', $progress);
        $this->assertArrayHasKey('pending', $progress);
        $this->assertArrayHasKey('percentage', $progress);
        $this->assertCount(2, $progress['completed']);
        $this->assertCount(1, $progress['pending']);
    }

    /** @test */
    public function it_returns_zero_percentage_when_no_contents()
    {
        $user = User::factory()->create();
        $training = Training::factory()->create();

        $this->actingAs($user);
        $progress = $training->progress();

        $this->assertEquals(0, $progress['percentage']);
    }
}


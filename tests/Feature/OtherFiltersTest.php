<?php

namespace Tests\Feature;

use App\Http\Livewire\IdeasIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Vote;
use App\Models\Idea;
use App\Models\Status;
use Livewire\Livewire;

class OtherFiltersTest extends TestCase
{
	use RefreshDatabase;

	/** @test*/
	public function top_voted_filter_works()
	{
		$user = User::factory()->create();
		$userB = User::factory()->create();
		$userC = User::factory()->create();

		$categoryOne = Category::factory()->create(['name' => 'Category 1']);

		$statusImplemented = Status::factory()->create(['id' => 4, 'name' => 'Implemented']);

		$ideaOne = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My First Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusImplemented->id,
			'description' => 'My first idea description',
		]);

		$ideaTwo = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My First Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusImplemented->id,
			'description' => 'My first idea description',
		]);

		Vote::factory()->create([
			'idea_id' => $ideaOne->id,
			'user_id' => $user->id,
		]);

		Vote::factory()->create([
			'idea_id' => $ideaOne->id,
			'user_id' => $userB->id,
		]);

		Vote::factory()->create([
			'idea_id' => $ideaTwo->id,
			'user_id' => $userC->id,
		]);

		Livewire::test(IdeasIndex::class)
			->set('filter', 'Top Voted')
			->assertViewHas('ideas', function ($ideas) {
				return $ideas->count() == 2
					&& $ideas->first()->votes()->count() == 2
					&& $ideas->get(1)->votes()->count() == 1;
			});
	}

	/** @test*/
	public function my_ideas_filter_works_correctly_when_user_logged_in()
	{
		$user = User::factory()->create();
		$userB = User::factory()->create();

		$categoryOne = Category::factory()->create(['name' => 'Category 1']);

		$statusImplemented = Status::factory()->create(['id' => 4, 'name' => 'Implemented']);

		$ideaOne = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My First Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusImplemented->id,
			'description' => 'My first idea description',
		]);

		$ideaTwo = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My Second Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusImplemented->id,
			'description' => 'My first idea description',
		]);

		$ideaThree = Idea::factory()->create([
			'user_id'     => $userB->id,
			'title'       => 'My Third Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusImplemented->id,
			'description' => 'My first idea description',
		]);

		Livewire::actingAs($user)
			->test(IdeasIndex::class)
			->set('filter', 'My Ideas')
			->assertViewHas('ideas', function ($ideas) {
				return $ideas->count() == 2
					&& $ideas->first()->title === 'My Second Idea'
					&& $ideas->get(1)->title === 'My First Idea';
			});
	}

	/** @test*/
	public function my_ideas_filter_works_correctly_when_user_is_not_logged_in()
	{
		$user = User::factory()->create();
		$userB = User::factory()->create();

		$categoryOne = Category::factory()->create(['name' => 'Category 1']);

		$statusImplemented = Status::factory()->create(['id' => 4, 'name' => 'Implemented']);

		$ideaOne = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My First Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusImplemented->id,
			'description' => 'My first idea description',
		]);

		$ideaTwo = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My Second Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusImplemented->id,
			'description' => 'My first idea description',
		]);

		$ideaThree = Idea::factory()->create([
			'user_id'     => $userB->id,
			'title'       => 'My Third Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusImplemented->id,
			'description' => 'My first idea description',
		]);

		Livewire::test(IdeasIndex::class)
			->set('filter', 'My Ideas')
			->assertRedirect(route('login'));
	}

	/** @test*/
	public function my_ideas_filter_works_correctly_with_categories_filter()
	{
		$user = User::factory()->create();

		$categoryOne = Category::factory()->create(['name' => 'Category 1']);
		$categoryTwo = Category::factory()->create(['name' => 'Category 2']);

		$statusImplemented = Status::factory()->create(['id' => 4, 'name' => 'Implemented']);

		$ideaOne = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My First Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusImplemented->id,
			'description' => 'My first idea description',
		]);

		$ideaTwo = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My Second Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusImplemented->id,
			'description' => 'My first idea description',
		]);

		$ideaThree = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My Third Idea',
			'category_id' => $categoryTwo->id,
			'status_id'   => $statusImplemented->id,
			'description' => 'My first idea description',
		]);

		Livewire::actingAs($user)
			->test(IdeasIndex::class)
			->set('category', 'Category 1')
			->set('filter', 'My Ideas')
			->assertViewHas('ideas', function ($ideas) {
				return $ideas->count() == 2
					&& $ideas->first()->title === 'My Second Idea'
					&& $ideas->get(1)->title === 'My First Idea';
			});
	}

	/** @test*/
	public function no_filters_works_correctly()
	{
		$user = User::factory()->create();

		$categoryOne = Category::factory()->create(['name' => 'Category 1']);

		$statusImplemented = Status::factory()->create(['id' => 4, 'name' => 'Implemented']);

		$ideaOne = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My First Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusImplemented->id,
			'description' => 'My first idea description',
		]);

		$ideaTwo = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My Second Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusImplemented->id,
			'description' => 'My first idea description',
		]);

		$ideaThree = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My Third Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusImplemented->id,
			'description' => 'My first idea description',
		]);

		Livewire::test(IdeasIndex::class)
			->set('filter', 'No Filter')
			->assertViewHas('ideas', function ($ideas) {
				return $ideas->count() == 3
					&& $ideas->first()->title === 'My Third Idea'
					&& $ideas->get(1)->title === 'My Second Idea';
			});
	}
}

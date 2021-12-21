<?php

namespace Tests\Feature;

use App\Http\Livewire\IdeaIndex;
use App\Http\Livewire\IdeasIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Idea;
use App\Models\Status;
use App\Models\Vote;
use Livewire\Livewire;

class VoteIndexPageTest extends TestCase
{
	use RefreshDatabase;

	/** @test*/
	public function index_page_contains_idea_index_livewire_component()
	{
		$user = User::factory()->create();

		$categoryOne = Category::factory()->create(['name' => 'Category 1']);

		$statusOpen = Status::factory()->create(['name' => 'Open', 'class' => 'bg-gray-200']);

		$idea = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My First Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusOpen->id,
			'description' => 'Description of my first idea',
		]);

		$this->get(route('idea.index'))
			->assertSeeLivewire('idea-index');
	}

	/** @test*/
	public function ideas_index_livewire_component_correctly_receives_votes_count()
	{
		$user = User::factory()->create();
		$userB = User::factory()->create();

		$categoryOne = Category::factory()->create(['name' => 'Category 1']);

		$statusOpen = Status::factory()->create(['name' => 'Open', 'class' => 'bg-gray-200']);

		$idea = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My First Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusOpen->id,
			'description' => 'Description of my first idea',
		]);

		Vote::factory()->create([
			'idea_id' => $idea->id,
			'user_id' => $user->id,
		]);

		Vote::factory()->create([
			'idea_id' => $idea->id,
			'user_id' => $userB->id,
		]);

		Livewire::test(IdeasIndex::class)
			->assertViewHas('ideas', function ($ideas) {
				return $ideas->first()->votes_count == 2;
			});
	}

	/** @test*/
	public function votes_count_shows_correctly_on_index_page_livewire_component()
	{
		$user = User::factory()->create();

		$categoryOne = Category::factory()->create(['name' => 'Category 1']);

		$statusOpen = Status::factory()->create(['name' => 'Open', 'class' => 'bg-gray-200']);

		$idea = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My First Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusOpen->id,
			'description' => 'Description of my first idea',
		]);

		Livewire::test(IdeaIndex::class, [
			'idea'       => $idea,
			'votesCount' => 5,
		])
		->assertSet('votesCount', 5);
	}

	/** @test*/
	public function user_who_is_logged_in_shows_voted_if_idea_already_voted_for()
	{
		$user = User::factory()->create();

		$categoryOne = Category::factory()->create(['name' => 'Category 1']);

		$statusOpen = Status::factory()->create(['name' => 'Open', 'class' => 'bg-gray-200']);

		$idea = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My First Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusOpen->id,
			'description' => 'Description of my first idea',
		]);

		Vote::factory()->create([
			'user_id' => $user->id,
			'idea_id' => $idea->id,
		]);

		$idea->votes_count = 1;
		$idea->voted_by_user = 1;

		/* @var mixed $user */
		Livewire::actingAs($user)
			->test(IdeaIndex::class, [
				'idea'       => $idea,
				'votesCount' => 5,
			])
			->assertSet('hasVoted', true)
			->assertSee('Voted');
	}

	/** @test*/
	public function user_who_is_not_logged_in_is_redirected_to_login_page_when_trying_to_vote()
	{
		$user = User::factory()->create();

		$categoryOne = Category::factory()->create(['name' => 'Category 1']);

		$statusOpen = Status::factory()->create(['name' => 'Open', 'class' => 'bg-gray-200']);

		$idea = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My First Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusOpen->id,
			'description' => 'Description of my first idea',
		]);

		Livewire::test(IdeaIndex::class, [
			'idea'       => $idea,
			'votesCount' => 5,
		])
		->call('vote')
		->assertRedirect(route('login'));
	}

	/** @test*/
	public function user_who_is_logged_in_can_vote_for_idea()
	{
		$user = User::factory()->create();

		$categoryOne = Category::factory()->create(['name' => 'Category 1']);

		$statusOpen = Status::factory()->create(['name' => 'Open', 'class' => 'bg-gray-200']);

		$idea = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My First Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusOpen->id,
			'description' => 'Description of my first idea',
		]);

		$this->assertDatabaseMissing('votes', [
			'idea_id' => $idea->id,
			'user_id' => $user->id,
		]);

		Livewire::actingAs($user)
			->test(IdeaIndex::class, [
				'idea'       => $idea,
				'votesCount' => 5,
			])
			->call('vote')
			->assertSet('votesCount', 6)
			->assertSet('hasVoted', true)
			->assertSee('Voted');

		$this->assertDatabaseHas('votes', [
			'idea_id' => $idea->id,
			'user_id' => $user->id,
		]);
	}

	/** @test*/
	public function user_who_is_logged_in_can_unvote_for_idea()
	{
		$user = User::factory()->create();

		$categoryOne = Category::factory()->create(['name' => 'Category 1']);

		$statusOpen = Status::factory()->create(['name' => 'Open', 'class' => 'bg-gray-200']);

		$idea = Idea::factory()->create([
			'user_id'     => $user->id,
			'title'       => 'My First Idea',
			'category_id' => $categoryOne->id,
			'status_id'   => $statusOpen->id,
			'description' => 'Description of my first idea',
		]);

		Vote::factory()->create([
			'user_id' => $user->id,
			'idea_id' => $idea->id,
		]);

		$idea->votes_count = 1;
		$idea->voted_by_user = 1;

		/* @var mixed $user */
		Livewire::actingAs($user)
			->test(IdeaIndex::class, [
				'idea'       => $idea,
				'votesCount' => 5,
			])
			->call('vote')
			->assertSet('votesCount', 4)
			->assertSet('hasVoted', false)
			->assertSee('Vote')
			->assertDontSee('Voted');

		$this->assertDatabaseMissing('votes', [
			'idea_id' => $idea->id,
			'user_id' => $user->id,
		]);
	}
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Idea;
use App\Models\Status;
use App\Models\Vote;
use Livewire\Livewire;
use App\Http\Livewire\IdeaShow;

class VoteShowPageTest extends TestCase
{
	use RefreshDatabase;

	/** @test*/
	public function show_page_contains_idea_show_livewire_component()
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

		$this->get(route('idea.show', $idea))
			->assertSeeLivewire('idea-show');
	}

	/** @test*/
	public function show_page_correctly_receives_votes_count()
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

		$this->get(route('idea.show', $idea))
			->assertViewHas('votesCount', 2);
	}

	/** @test*/
	public function votes_count_shows_correctly_on_show_page_livewire_component()
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

		Livewire::test(IdeaShow::class, [
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

		Livewire::actingAs($user)
		->test(IdeaShow::class, [
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

		Livewire::test(IdeaShow::class, [
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
			->test(IdeaShow::class, [
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
	public function user_who_is_logged_in_can_remove_vote_for_idea()
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

		Livewire::actingAs($user)
			->test(IdeaShow::class, [
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
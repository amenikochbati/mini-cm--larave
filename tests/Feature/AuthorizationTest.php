<?php

use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

function authorizationPostData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Article protégé',
        'slug' => 'article-protege',
        'body' => 'Contenu assez long pour passer la validation.',
        'is_published' => '1',
    ], $overrides);
}

it('redirects a guest to the login page when editing a post', function () {
    $post = Post::factory()->create();

    $this->get(route('posts.edit', $post))
        ->assertRedirect(route('login'));
});

it('records the logged-in user as the author of a new post', function () {
    $author = User::factory()->create();

    $this->actingAs($author)
        ->post(route('posts.store'), authorizationPostData())
        ->assertRedirect();

    $this->assertDatabaseHas('posts', [
        'slug' => 'article-protege',
        'user_id' => $author->id,
    ]);
});

it('allows an author to edit their own post', function () {
    $author = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $author->id]);

    $this->actingAs($author)
        ->get(route('posts.edit', $post))
        ->assertOk();
});

it('forbids an author from editing another authors post', function () {
    $post = Post::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs(User::factory()->create())
        ->get(route('posts.edit', $post))
        ->assertForbidden();
});

it('forbids an author from updating another authors post', function () {
    $post = Post::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs(User::factory()->create())
        ->put(route('posts.update', $post), authorizationPostData())
        ->assertForbidden();

    $this->assertDatabaseMissing('posts', ['slug' => 'article-protege']);
});

it('forbids an author from deleting another authors post', function () {
    $post = Post::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs(User::factory()->create())
        ->delete(route('posts.destroy', $post))
        ->assertForbidden();

    $this->assertDatabaseHas('posts', ['id' => $post->id]);
});

it('allows an administrator to update any post', function () {
    $post = Post::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('posts.update', $post), authorizationPostData())
        ->assertRedirect(route('posts.show', $post));

    $this->assertDatabaseHas('posts', ['id' => $post->id, 'slug' => 'article-protege']);
});

it('restricts category management to administrators', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.categories.index'))
        ->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.categories.index'))
        ->assertOk();

    $this->assertDatabaseCount('categories', 0);
});

it('hides post actions from users who cannot update the post', function () {
    $owner = User::factory()->create();
    $post = Post::factory()->published()->create(['user_id' => $owner->id]);

    $this->actingAs(User::factory()->create())
        ->get(route('posts.show', $post))
        ->assertOk()
        ->assertDontSee('Modifier')
        ->assertDontSee('Supprimer');
});

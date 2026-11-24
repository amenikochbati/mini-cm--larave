<?php

use App\Models\Post;

beforeEach(function () {
    $this->withoutVite();
});

it('lists published posts in a paginated resource', function () {
    Post::factory()->published()->create(['title' => 'Article public']);
    Post::factory()->create(['title' => 'Brouillon privé', 'is_published' => false]);

    $this->getJson('/api/posts?per_page=2')
        ->assertOk()
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonFragment(['title' => 'Article public'])
        ->assertJsonMissing(['title' => 'Brouillon privé'])
        ->assertJsonMissing(['body' => '']);
});

it('shows a published post with its author category and tags', function () {
    $post = Post::factory()->published()->create();

    $this->getJson(route('api.posts.show', $post))
        ->assertOk()
        ->assertJsonStructure([
            'data' => ['id', 'title', 'slug', 'excerpt', 'body', 'published_at', 'author', 'category', 'tags', 'url'],
        ]);
});

it('returns 404 for an unknown post', function () {
    $this->getJson('/api/posts/999999')
        ->assertNotFound();
});

it('forbids a draft post to a guest', function () {
    $post = Post::factory()->create(['is_published' => false]);

    $this->getJson(route('api.posts.show', $post))
        ->assertForbidden();
});

it('rejects an invalid per_page value', function () {
    $this->getJson('/api/posts?per_page=500')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['per_page']);
});

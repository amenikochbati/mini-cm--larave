<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;

it('relates posts to their optional author and category', function () {
    $category = Category::factory()->create();
    $user = User::factory()->create();

    $post = $user->posts()->create([
        'title' => 'Article avec relations',
        'slug' => 'article-avec-relations',
        'body' => 'Contenu de test.',
        'category_id' => $category->id,
    ]);

    expect($post->user->is($user))->toBeTrue()
        ->and($post->category->is($category))->toBeTrue()
        ->and($user->posts->contains($post))->toBeTrue()
        ->and($category->posts->contains($post))->toBeTrue();
});

it('allows posts without an author or category', function () {
    $post = Post::create([
        'title' => 'Article sans relations',
        'slug' => 'article-sans-relations',
        'body' => 'Contenu de test.',
    ]);

    expect($post->user)->toBeNull()
        ->and($post->category)->toBeNull();
});

it('preserves posts and clears foreign keys when related models are deleted', function () {
    $category = Category::factory()->create();
    $user = User::factory()->create();
    $post = $user->posts()->create([
        'title' => 'Article à conserver',
        'slug' => 'article-a-conserver',
        'body' => 'Contenu de test.',
        'category_id' => $category->id,
    ]);

    $category->delete();
    $user->delete();
    $post->refresh();

    expect($post->user_id)->toBeNull()
        ->and($post->category_id)->toBeNull()
        ->and($post->exists)->toBeTrue();
});

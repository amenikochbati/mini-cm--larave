<?php

use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
    $this->actingAs(User::factory()->admin()->create());
});

function feedbackPostData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Article avec message',
        'slug' => 'article-avec-message',
        'body' => 'Contenu assez long pour passer la validation.',
        'is_published' => '1',
    ], $overrides);
}

it('shows a success message once after creating a post', function () {
    $this->post(route('posts.store'), feedbackPostData())
        ->assertRedirect(route('posts.show', 1))
        ->assertSessionHas('success', 'Article créé.');

    $this->get(route('posts.show', 1))->assertOk()->assertSee('Article créé.');

    $this->get(route('posts.show', 1))->assertDontSee('Article créé.');
});

it('shows a success message after updating a post', function () {
    $post = Post::factory()->create();

    $this->put(route('posts.update', $post), feedbackPostData([
        'title' => 'Article modifié',
        'slug' => $post->slug,
    ]))->assertRedirect(route('posts.show', $post))
        ->assertSessionHas('success', 'Article mis à jour.');
});

it('shows a success message after deleting a post', function () {
    $post = Post::factory()->create();

    $this->delete(route('posts.destroy', $post))
        ->assertRedirect(route('posts.index'))
        ->assertSessionHas('success', 'Article supprimé.');

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

it('shows a success message after changing publication status', function () {
    $post = Post::factory()->create();

    $this->patch(route('posts.publication', $post))
        ->assertRedirect(route('posts.show', $post))
        ->assertSessionHas('success', 'Article publié.');

    expect($post->fresh()->is_published)->toBeTrue();
});

it('shows the custom page for an unknown post', function () {
    $this->get('/posts/999')
        ->assertNotFound()
        ->assertSee('Article introuvable');
});

it('keeps the standard not-found page for an unknown tag', function () {
    $this->get('/posts/tag/inconnu')
        ->assertNotFound()
        ->assertDontSee('Article introuvable')
        ->assertDontSee('Catégorie introuvable');
});

it('shows a read-only banner on the home page when writes are disabled', function () {
    config(['site.read_only' => true]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Site en lecture seule');
});

it('shows only recent published posts on the custom post not-found page', function () {
    Post::factory()->published()->create(['title' => 'Article récent publié']);
    Post::factory()->create(['title' => 'Brouillon récent']);

    $this->get('/posts/999')
        ->assertNotFound()
        ->assertSee('Articles récents')
        ->assertSee('Article récent publié')
        ->assertDontSee('Brouillon récent');
});

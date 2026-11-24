<?php

use App\Models\Category;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

it('shows the styled 404 page for an unknown URL', function () {
    $this->get('/page-inexistante')
        ->assertNotFound()
        ->assertSee('Page introuvable')
        ->assertSee("Retour à l'accueil", false);
});

it('shows the styled 503 page when the site is read-only', function () {
    config(['site.read_only' => true]);

    $this->actingAs(User::factory()->create())
        ->post(route('posts.store'), [])
        ->assertStatus(503)
        ->assertSee('Service momentanément indisponible')
        ->assertSee('Le site est en lecture seule.')
        ->assertSee("Retour à l'accueil", false);
});

it('shows a helpful category not-found page with available categories', function () {
    Category::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

    $this->get('/posts/categorie/inconnue')
        ->assertNotFound()
        ->assertSee('Catégorie introuvable')
        ->assertSee('Laravel')
        ->assertSee('/posts/categorie/laravel');
});

it('shows an article-specific 404 page for an unknown post', function () {
    $this->get('/posts/999')
        ->assertNotFound()
        ->assertSee('Article introuvable')
        ->assertDontSee('Page introuvable');
});

it('still returns a standard 404 for an unknown tag', function () {
    $this->get('/posts/tag/inconnu')
        ->assertNotFound()
        ->assertSee('Page introuvable')
        ->assertDontSee('Catégorie introuvable');
});

it('shows the read-only banner while keeping read pages available', function () {
    config(['site.read_only' => true]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->assertSee('Site en lecture seule');
});

it('keeps not-found responses JSON for JSON requests', function () {
    $this->get('/posts/999', ['Accept' => 'application/json'])
        ->assertNotFound()
        ->assertJsonStructure(['message']);

    $this->get('/posts/categorie/inconnue', ['Accept' => 'application/json'])
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});

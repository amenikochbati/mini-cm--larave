<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->withoutVite();
});

it('shows the posts list', function () {
    Post::create([
        'title' => 'Premier article',
        'slug' => 'premier-article',
        'body' => 'Contenu du premier article de test.',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Premier article');
});

it('shows a message when there is no post', function () {
    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Aucun article');
});

it('shows a single post', function () {
    $post = Post::create([
        'title' => 'Article complet',
        'slug' => 'article-complet',
        'body' => 'Texte complet de cet article de test.',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('Texte complet de cet article de test.');
});

it('shows category, author, and tags on an article detail page', function () {
    $category = Category::factory()->create(['name' => 'Laravel']);
    $user = User::factory()->create(['name' => 'Auteure de test']);
    $tag = Tag::factory()->create(['name' => 'Performance']);
    $post = $user->posts()->create([
        'title' => 'Article avec relations',
        'slug' => 'article-avec-relations',
        'body' => 'Contenu de test pour la page de détail.',
        'is_published' => true,
        'published_at' => now(),
        'category_id' => $category->id,
    ]);
    $post->tags()->attach($tag);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('Laravel')
        ->assertSee('Auteure de test')
        ->assertSee('Performance');
});

it('shows other articles from the same category on the detail page', function () {
    $category = Category::factory()->create(['name' => 'Laravel']);
    $otherCategory = Category::factory()->create(['name' => 'PHP']);
    $post = Post::factory()->create([
        'title' => 'Article courant',
        'slug' => 'article-courant',
        'is_published' => true,
        'published_at' => now(),
        'category_id' => $category->id,
    ]);
    Post::factory()->published()->create([
        'title' => 'Autre article Laravel',
        'slug' => 'autre-article-laravel',
        'category_id' => $category->id,
    ]);
    Post::factory()->published()->create([
        'title' => 'Article PHP distinct',
        'slug' => 'article-php-distinct',
        'category_id' => $otherCategory->id,
    ]);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('Dans la même catégorie')
        ->assertSee('Autre article Laravel')
        ->assertDontSee('Article PHP distinct');
});

it('loads post relationships in a fixed number of queries', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $tag = Tag::factory()->create();
    Post::factory()->count(4)->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ])->each(fn (Post $post) => $post->tags()->attach($tag));

    DB::flushQueryLog();
    DB::enableQueryLog();

    $posts = Post::with('user', 'category', 'tags')->latest('id')->take(4)->get();
    $posts->each(fn (Post $post) => [$post->user, $post->category, $post->tags]);
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBe(4);
});

it('rejects lazy loading outside production', function () {
    expect(Model::preventsLazyLoading())->toBeTrue();

    $posts = Post::factory()->count(2)->create();
    $posts = Post::whereKey($posts->modelKeys())->get();

    expect(fn () => $posts->each(fn (Post $post) => $post->category))
        ->toThrow(LazyLoadingViolationException::class);
});

it('shows an article category, author, and tags on its card', function () {
    $category = Category::factory()->create(['name' => 'Laravel']);
    $user = User::factory()->create(['name' => 'Auteure de test']);
    $tag = Tag::factory()->create(['name' => 'Performance']);
    $post = $user->posts()->create([
        'title' => 'Article avec métadonnées',
        'slug' => 'article-avec-metadonnees',
        'body' => 'Contenu de test pour la carte.',
        'is_published' => true,
        'published_at' => now(),
        'category_id' => $category->id,
    ]);
    $post->tags()->attach($tag);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Laravel')
        ->assertSee('Auteure de test')
        ->assertSee('Performance');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Laravel')
        ->assertSee('Auteure de test')
        ->assertSee('Performance');
});

it('shows only published posts and their relationships in an archive', function () {
    $category = Category::factory()->create(['name' => 'Archives']);
    $user = User::factory()->create(['name' => 'Auteur archive']);
    $tag = Tag::factory()->create(['name' => 'Archive tag']);
    $post = $user->posts()->create([
        'title' => 'Article archivé',
        'slug' => 'article-archive',
        'body' => 'Contenu publié pour tester la page archive.',
        'is_published' => true,
        'published_at' => now(),
        'category_id' => $category->id,
    ]);
    $post->tags()->attach($tag);
    Post::factory()->create([
        'title' => 'Brouillon de l’archive',
        'slug' => 'brouillon-archive',
        'is_published' => false,
        'published_at' => now(),
    ]);

    $this->get(route('posts.archive', now()->year))
        ->assertOk()
        ->assertSee('Article archivé')
        ->assertSee('Archives')
        ->assertSee('Auteur archive')
        ->assertSee('Archive tag')
        ->assertDontSee('Brouillon de l’archive');
});

it('returns 404 for an unknown post', function () {
    $this->get('/posts/999')->assertNotFound();
});

it('shows the create form', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('posts.create'))
        ->assertOk()
        ->assertSee('Nouvel article');
});

it('validates and stores a new post', function () {
    $this->actingAs(User::factory()->create())
        ->from(route('posts.create'))
        ->post(route('posts.store'), [
            'title' => 'Un article créé en test',
            'slug' => 'article-cree-en-test',
            'excerpt' => 'Un court extrait de test.',
            'body' => 'Un contenu suffisamment long pour être validé.',
            'is_published' => '1',
        ])
        ->assertRedirect(route('posts.show', 1));

    $this->assertDatabaseHas('posts', [
        'slug' => 'article-cree-en-test',
        'excerpt' => 'Un court extrait de test.',
        'is_published' => true,
    ]);
});

it('rejects invalid post input', function () {
    $this->actingAs(User::factory()->create())
        ->from(route('posts.create'))
        ->post(route('posts.store'), [
            'title' => '',
            'slug' => 'Invalid Slug',
            'body' => 'Too short',
        ])
        ->assertRedirect(route('posts.create'))
        ->assertSessionHasErrors([
            'title' => 'Le champ titre est obligatoire.',
            'slug' => 'Le format du champ slug est invalide.',
            'body' => 'Le champ contenu doit contenir au moins 20 caractères.',
        ]);

    $this->assertDatabaseCount('posts', 0);
});

it('shows only published posts on the home page', function () {
    Post::create([
        'title' => 'Article visible',
        'slug' => 'article-visible',
        'body' => 'Cet article est publie et doit apparaitre.',
        'is_published' => true,
        'published_at' => now(),
    ]);

    Post::create([
        'title' => 'Article masque',
        'slug' => 'article-masque',
        'body' => 'Cet article est un brouillon et reste cache.',
        'is_published' => false,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Article visible')
        ->assertDontSee('Article masque');
});

it('limits home page posts to the three most recently published', function () {
    foreach ([
        ['Article le plus ancien', 'article-plus-ancien', now()->subDays(4)],
        ['Article récent 3', 'article-recent-3', now()->subDays(3)],
        ['Article récent 2', 'article-recent-2', now()->subDays(2)],
        ['Article récent 1', 'article-recent-1', now()->subDay()],
    ] as [$title, $slug, $publishedAt]) {
        Post::create([
            'title' => $title,
            'slug' => $slug,
            'body' => 'Contenu assez long pour créer cet article publié.',
            'is_published' => true,
            'published_at' => $publishedAt,
        ]);
    }

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['Article récent 1', 'Article récent 2', 'Article récent 3'])
        ->assertDontSee('Article le plus ancien');
});

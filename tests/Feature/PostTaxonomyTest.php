<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
    $this->actingAs(User::factory()->admin()->create());
});

function taxonomyPostData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Article classé',
        'slug' => 'article-classe',
        'body' => 'Contenu assez long pour passer la validation.',
        'is_published' => '1',
    ], $overrides);
}

it('stores the category and tags selected in the form', function () {
    $category = Category::factory()->create();
    $tags = Tag::factory(2)->create();

    $this->post(route('posts.store'), taxonomyPostData([
        'category_id' => $category->id,
        'tags' => $tags->modelKeys(),
    ]))->assertRedirect(route('posts.show', 1));

    $post = Post::where('slug', 'article-classe')->firstOrFail();

    expect($post->category_id)->toBe($category->id)
        ->and($post->tags()->count())->toBe(2);
});

it('replaces post tags on update', function () {
    $post = Post::factory()->create(['slug' => 'article-classe']);
    [$oldTag, $keptTag, $newTag] = Tag::factory(3)->create()->all();
    $post->tags()->attach([$oldTag->id, $keptTag->id]);

    $this->put(route('posts.update', $post), taxonomyPostData([
        'tags' => [$keptTag->id, $newTag->id],
    ]))->assertRedirect(route('posts.show', $post));

    $this->assertDatabaseMissing('post_tag', ['post_id' => $post->id, 'tag_id' => $oldTag->id]);
    $this->assertDatabaseHas('post_tag', ['post_id' => $post->id, 'tag_id' => $keptTag->id]);
    $this->assertDatabaseHas('post_tag', ['post_id' => $post->id, 'tag_id' => $newTag->id]);
});

it('removes all tags when no checkbox is selected', function () {
    $post = Post::factory()->create(['slug' => 'article-classe']);
    $post->tags()->attach(Tag::factory(2)->create());

    $this->put(route('posts.update', $post), taxonomyPostData())
        ->assertRedirect(route('posts.show', $post));

    expect($post->tags()->count())->toBe(0);
});

it('rejects a category that does not exist', function () {
    $this->post(route('posts.store'), taxonomyPostData(['category_id' => 999]))
        ->assertRedirect()
        ->assertSessionHasErrors('category_id');

    $this->assertDatabaseMissing('posts', ['slug' => 'article-classe']);
});

it('lists only published posts for a category found by slug', function () {
    $category = Category::factory()->create(['name' => 'Tutoriels', 'slug' => 'tutoriels']);
    Post::factory()->published()->create(['category_id' => $category->id, 'title' => 'Dans la catégorie']);
    Post::factory()->create(['category_id' => $category->id, 'title' => 'Brouillon de la catégorie']);
    Post::factory()->published()->create(['title' => 'Hors catégorie']);

    $this->get('/posts/categorie/tutoriels')
        ->assertOk()
        ->assertSee('Dans la catégorie')
        ->assertDontSee('Brouillon de la catégorie')
        ->assertDontSee('Hors catégorie');
});

it('returns not found for an unknown category slug', function () {
    $this->get('/posts/categorie/inconnue')->assertNotFound();
});

it('counts only published posts per category on the home page', function () {
    $category = Category::factory()->create(['name' => 'Tutoriels']);
    Post::factory(2)->published()->create(['category_id' => $category->id]);
    Post::factory()->create(['category_id' => $category->id]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Tutoriels (2)');
});

it('lists only published posts for a tag found by slug', function () {
    $tag = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $otherTag = Tag::factory()->create(['name' => 'PHP', 'slug' => 'php']);
    $matchingPost = Post::factory()->published()->create(['title' => 'Article avec Laravel']);
    $matchingPost->tags()->attach($tag);
    $draft = Post::factory()->create(['title' => 'Brouillon Laravel']);
    $draft->tags()->attach($tag);
    $otherPost = Post::factory()->published()->create(['title' => 'Article PHP']);
    $otherPost->tags()->attach($otherTag);

    $this->get('/posts/tag/laravel')
        ->assertOk()
        ->assertSee('Article avec Laravel')
        ->assertDontSee('Brouillon Laravel')
        ->assertDontSee('Article PHP');
});

it('returns not found for an unknown tag slug', function () {
    $this->get('/posts/tag/inconnu')->assertNotFound();
});

it('shows popular tags in count and name order and hides tags without published posts', function () {
    $popularTag = Tag::factory()->create(['name' => 'Zèbre', 'slug' => 'zebre']);
    $sameCountTag = Tag::factory()->create(['name' => 'Astuce', 'slug' => 'astuce']);
    $draftOnlyTag = Tag::factory()->create(['name' => 'Brouillon', 'slug' => 'brouillon']);

    Post::factory(2)->published()->create()->each(fn (Post $post) => $post->tags()->attach($popularTag));
    Post::factory(2)->published()->create()->each(fn (Post $post) => $post->tags()->attach($sameCountTag));
    Post::factory()->create()->tags()->attach($draftOnlyTag);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['Astuce (2)', 'Zèbre (2)'])
        ->assertDontSee('Brouillon (0)');
});

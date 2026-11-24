<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        fake()->seed(20261028);

        User::factory()->admin()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory(2)->create();

        $this->call([
            CategorySeeder::class,
            TagSeeder::class,
        ]);

        $users = User::query()->orderBy('id')->get();
        $categories = Category::query()->orderBy('id')->get();
        $tags = Tag::query()->orderBy('id')->get();

        for ($index = 0; $index < 12; $index++) {
            $isPublished = $index < 9;
            $factory = Post::factory()->state([
                'user_id' => $users[$index % $users->count()]->id,
                'category_id' => $categories[$index % $categories->count()]->id,
            ]);

            if ($isPublished) {
                $publishedAt = now()->startOfDay()->subDays($index + 1);
                $factory = $factory->published()->state([
                    'published_at' => $publishedAt,
                ]);
            }

            $post = $factory->create();
            $post->tags()->sync([
                $tags[$index % $tags->count()]->id,
                $tags[($index + 1) % $tags->count()]->id,
            ]);
        }
    }
}

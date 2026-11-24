<x-layout>
    <x-slot:title>Catégorie introuvable - Mini-CMS</x-slot:title>

    <h1 class="text-3xl font-bold text-gray-900">Catégorie introuvable</h1>
    <p class="mt-2 mb-8 text-gray-600">Cette catégorie n'existe pas ou a été supprimée.</p>

    <x-alert type="info" title="Catégories disponibles" class="max-w-2xl">
        @if ($categories->isEmpty())
            Aucune catégorie n'est disponible pour le moment.
        @else
            <ul class="mt-2 list-inside list-disc">
                @foreach ($categories as $category)
                    <li><a href="{{ route('posts.category', $category) }}" class="underline hover:text-indigo-600">{{ $category->name }}</a></li>
                @endforeach
            </ul>
        @endif
    </x-alert>

    <p class="mt-6">
        <a href="{{ route('home') }}" class="font-medium text-indigo-600 hover:underline">Retour à l'accueil</a>
    </p>
</x-layout>

<option value="{{ $category->id }}" 
    {{ isset($selectedCategory) && $selectedCategory == $category->id ? 'selected' : '' }}>
    {{ $prefix }}{{ $category->name }}
</option>

@if ($category->childrenRecursive->count())
    @foreach ($category->childrenRecursive as $subcategory)
        @include('categories.category-options', [
            'category' => $subcategory, 
            'prefix' => $prefix . '— ', 
            'selectedCategory' => $selectedCategory ?? null
        ])
    @endforeach
@endif

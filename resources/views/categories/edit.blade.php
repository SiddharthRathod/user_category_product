
@extends('layouts.webapp')

@section('content')

@include('components.flash-message')

<div class="container">
    <h2>Edit Category</h2>
    <form action="{{ route('categories.update', $category->id) }}" method="POST" id="categoryForm">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="name" class="form-control" value="{{ $category->name }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Parent Category (Optional)</label>
            <select name="parent_id" id="parent-category" class="form-control">
                <option value="">None</option>
                @foreach ($categories as $cat)
                    @include('categories.category-options', [
                        'category' => $cat, 
                        'prefix' => '', 
                        'selectedCategory' => $category->parent_id
                    ])
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="active" {{ $category->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $category->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@push('script')
<script>
    $(document).ready(function() {
        $("#categoryForm").validate({
            rules: {
                name: {
                    required: true,
                    minlength: 3
                },
                status: {
                    required: true
                }
            },
            messages: {
                name: {
                    required: "Category name is required",
                    minlength: "Category name must be at least 3 characters"
                },
                status: {
                    required: "Please select a status"
                }
            },
            errorElement: "div",
            errorPlacement: function(error, element) {
                error.addClass("text-danger");
                element.closest(".mb-3").append(error);
            }
        });
    });
</script>
@endpush
@endsection

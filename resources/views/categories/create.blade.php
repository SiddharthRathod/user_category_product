@extends('layouts.webapp')

@section('content')

@include('components.flash-message')

<div class="container">
    <h2>Add Category</h2>
    <form action="{{ route('categories.store') }}" method="POST" id="categoryForm">
        @csrf
        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="name" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Parent Category (Optional)</label>
            <select name="parent_id" class="form-control">
                <option value="">None</option>
                @foreach ($categories as $category)
                    @include('categories.category-options', ['category' => $category, 'prefix' => ''])
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Save</button>
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
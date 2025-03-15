@extends('layouts.webapp')

@section('content')

@include('components.flash-message')

<div class="container">
    <h2>Add Product</h2>
    <form action="{{ route('products.store') }}" method="POST" id="productForm">
        @csrf

        <div class="mb-3">
            <label class="form-label">Main Category</label>
            <select id="main-category" class="form-control" name="category_id">
                <option value="">Select Category</option>
                @foreach ($categories as $category)
                    @include('categories.category-options', ['category' => $category, 'prefix' => ''])
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" name="name" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Product Price</label>
            <input type="text" name="price" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="active" {{ old('status', $product->status ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $product->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>


        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

@push('script')
    <script>
        $(document).ready(function() {
            $("#productForm").validate({
                rules: {
                    category_id: "required",
                    name: { required: true, minlength: 3 },
                    price: { required: true, number: true },
                    quantity: { 
                        required: true, 
                        digits: true, 
                        max: 2147483647 
                    }
                },
                messages: {
                    category_id: "Please select a category",
                    name: {
                        required: "Product name is required",
                        minlength: "Product name must be at least 3 characters"
                    },
                    price: {
                        required: "Price is required",
                        number: "Please enter a valid number"
                    },
                    quantity: {
                        required: "Quantity is required",
                        digits: "Only whole numbers are allowed",
                        max: "Quantity cannot be greater than 2,147,483,647"
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
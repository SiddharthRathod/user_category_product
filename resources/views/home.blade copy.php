@extends('layouts.webapp')

@section('content')
<div class="container mt-4">
    <h2 class="text-center">Product Listings</h2>

    <div class="row mb-3">
        <div class="col-md-4">
            <label>Category:</label>
            <select id="category-filter" class="form-control">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label>Date Range:</label>
            <input type="text" id="date-range" class="form-control">
        </div>

        <div class="col-md-4">
            <button id="filter-btn" class="btn btn-success mt-4">Apply Filter</button>
        </div>
    </div>

    <table id="products-table" class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Category</th>
                <th>Owner</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>
</div>

@push('script')
<script>
    $(document).ready(function() {
        let table = $('#products-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('products.data') }}",
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                    d.category_id = $('#category-filter').val();
                    let dateRange = $('#date-range').val().split(' - ');
                    d.start_date = dateRange[0] || null;
                    d.end_date = dateRange[1] || null;
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'category', name: 'category.name' },
                { data: 'user', name: 'user.name' },
                { data: 'price', name: 'price' },
                { data: 'quantity', name: 'quantity' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[6, 'desc']],
            pageLength: 10
        });

        // Apply Filters
        $('#filter-btn').on('click', function() {
            table.ajax.reload();
        });

        // Date Range Picker
        $('#date-range').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear' }
        });

        $('#date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
</script>
@endpush

@endsection

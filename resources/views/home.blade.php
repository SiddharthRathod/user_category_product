@extends('layouts.webapp')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Available Products</h2>

    <div class="row mb-3">
        <div class="col-md-4">
            <label>Filter by Category:</label>
            <select id="categoryFilter" class="form-control">
                <option value="">All Categories</option>
                {{-- Fetch categories dynamically --}}
                @foreach(App\Models\Category::all() as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label>Date Range:</label>
            <input type="text" id="dateRange" class="form-control">
        </div>
    </div>

    <table id="productsTable" class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>User</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>
</div>
@push('script')
    <script>
    $(document).ready(function() {
        let table = $('#productsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('products.data') }}",
                data: function(d) {
                    d.category_id = $('#categoryFilter').val();
                    let dateRange = $('#dateRange').val();
                    if (dateRange) {
                        let dates = dateRange.split(' - ');
                        d.date_from = dates[0];
                        d.date_to = dates[1];
                    }
                }
            },
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'category', orderable: false },
                { data: 'price' },
                { data: 'quantity' },
                { data: 'user', orderable: false },
                { data: 'created_at' },
                { data: 'action', orderable: false, searchable: false }
            ]
        });

        $('#categoryFilter, #dateRange').change(function() {
            table.draw();
        });

        $('#dateRange').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear' }
        });

        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            table.draw();
        });

        $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            table.draw();
        });
    });
    </script>
@endpush
@endsection
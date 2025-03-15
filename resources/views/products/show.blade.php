@extends('layouts.webapp')

@section('content')
<div class="container">
    <h2 class="mb-3">Product Details</h2>
    
    <div class="card">
        <div class="card-body">
            <h4>{{ $product->name }}</h4>
            <p><strong>Category:</strong> {{ $product->category->name ?? 'N/A' }}</p>
            <p><strong>Added By:</strong> {{ $product->user->name ?? 'Unknown' }}</p>
            <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
            <p><strong>Quantity:</strong> {{ $product->quantity }}</p>
            <p><strong>Status:</strong> {{ ucfirst($product->status) }}</p>
            <p><strong>Created At:</strong> {{ $product->created_at->format('d M Y, h:i A') }}</p>
            
            <a href="{{ route('home') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection

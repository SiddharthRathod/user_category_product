<?php

namespace App\Http\Controllers;


use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Product::class);
        $products = Product::where('user_id', Auth::id())->get();
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Product::class);
        $categories = Category::where('status','active')->whereNull('parent_id')->get();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Product::class);
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|unique:products|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        Product::create([
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $request->quantity,
        ]);

        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        $categories = Category::whereNull('parent_id')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|unique:products,name,' . $product->id,
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        $product->update($request->all());

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function productDashboard(){
        
        $categories = Category::where('status', 'active')->get();
        $products = Product::with(['category', 'user'])->latest()->paginate(3);

        return view('home', compact('categories', 'products'));
    }

    public function getProductsData(Request $request)
    {

        \Log::info('Request Data:', $request->all()); // Debug request data
        
        $query = Product::with(['category', 'user']);

        // Apply Category Filter
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        // Apply Date Range Filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        return DataTables::of($query)
            ->addColumn('user', function ($product) {
                return $product->user ? $product->user->name : 'N/A';
            })
            ->addColumn('category', function ($product) {
                return $product->category ? $product->category->name : 'N/A';
            })
            ->addColumn('action', function ($product) {
                return '<a href="#" class="btn btn-primary btn-sm">View</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

}

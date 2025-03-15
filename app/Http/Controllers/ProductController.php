<?php

namespace App\Http\Controllers;


use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\ProductRequest;

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
    public function store(ProductRequest $request)
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
    public function show(Product $product, $id)
    {
        $product = Product::with('category', 'user')->findOrFail($id);
        return view('products.show', compact('product'));
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
    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);
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

    /**
     * Display the product dashboard.
     * 
     * This function fetches active categories and the latest products 
     * with pagination for display on the home page.
    */
    public function productDashboard() {        
        return view('home');
    }

    /**
     * Fetch products with filtering options for DataTables.
     * 
     * This function supports:
     * - Filtering by category
     * - Filtering by date range
     * - Fetching related user and category information
    */
    public function getProducts(Request $request)
    {
        
        $products = Product::with('category', 'user')
            ->where('status', 'active') 
            ->whereHas('category', function ($query) {
                $query->where('status', 'active');
            })
            ->when($request->category_id, fn($query) => $query->where('category_id', $request->category_id))
            ->when($request->date_from && $request->date_to, function ($query) use ($request) {
                $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
            });

        return DataTables::of($products)
            ->addColumn('category', fn($product) => $product->category->name ?? 'N/A')
            ->addColumn('user', fn($product) => $product->user->name ?? 'Unknown')
            ->editColumn('created_at', fn($product) => $product->created_at->format('d M Y, h:i A'))
            ->addColumn('action', function ($product) {
                return '<a href="' . route('products.show', $product->id) . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

}

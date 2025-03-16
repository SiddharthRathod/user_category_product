<?php
   
namespace App\Http\Controllers\Api;
   
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\Product;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {

        $cacheKey = 'products_list_' . md5(json_encode($request->all()));

        $products = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($request) {
            return Product::with('category', 'user')
                ->where('status', 'active')
                ->whereHas('category', function ($query) {
                    $query->where('status', 'active');
                })
                ->when($request->category_id, fn($query) => $query->where('category_id', $request->category_id))
                ->when($request->date_from && $request->date_to, function ($query) use ($request) {
                    $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
                })->get();
        });
    
        return $this->sendResponse($products, 'Products retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('products', 'name'),
            ],
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1|max:2147483647',
            'status' => 'required|in:active,inactive',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation  Error.', $validator->errors());       
        } 

        $product = Product::create([
                        'user_id' => Auth::id(),
                        'category_id' => $request->category_id,
                        'name' => $request->name,
                        'price' => $request->price,
                        'quantity' => $request->quantity,
                    ]);
        
        return $this->sendResponse($product, 'Product created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): JsonResponse
    {
        $product = Product::find($id);
  
        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }
   
        return $this->sendResponse($product, 'Product retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('products', 'name')->ignore($product->id),
            ],
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1|max:2147483647',
            'status' => 'required|in:active,inactive',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation  Error.', $validator->errors());       
        } 
   
        $product->update($request->all());
   
        return $this->sendResponse($product, 'Product updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
   
        return $this->sendResponse([], 'Product deleted successfully.');
    }
}
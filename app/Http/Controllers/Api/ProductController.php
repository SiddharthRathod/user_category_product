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

/**
 * @OA\Schema(
 *     schema="Product",
 *     title="Product",
 *     description="Product model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=2),
 *     @OA\Property(property="category_id", type="integer", example=3),
 *     @OA\Property(property="name", type="string", example="Product Name"),
 *     @OA\Property(property="price", type="number", format="float", example=99.99),
 *     @OA\Property(property="quantity", type="integer", example=10),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-16T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-16T12:34:56Z"),
 * )
*/
class ProductController extends BaseController
{
    /**
     * @OA\Get(
     *      path="/api/products",
     *      operationId="getProductsList",
     *      tags={"Products"},
     *      summary="Get a list of products",
     *      description="Returns a list of active products, filtered by category or date range.",
     *      @OA\Parameter(
     *          name="category_id",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer"),
     *          description="Filter products by category ID"
     *      ),
     *      @OA\Parameter(
     *          name="date_from",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="string", format="date"),
     *          description="Filter products created from this date"
     *      ),
     *      @OA\Parameter(
     *          name="date_to",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="string", format="date"),
     *          description="Filter products created up to this date"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Products retrieved successfully",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Product"))
     *      )
     * )
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
     * @OA\Post(
     *      path="/api/products",
     *      operationId="createProduct",
     *      tags={"Products"},
     *      summary="Create a new product",
     *      description="Stores a new product in the database.",
     *      security={{ "bearerAuth":{} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"category_id", "name", "price", "quantity", "status"},
     *              @OA\Property(property="category_id", type="integer", example=1),
     *              @OA\Property(property="name", type="string", example="New Product"),
     *              @OA\Property(property="price", type="number", format="float", example=99.99),
     *              @OA\Property(property="quantity", type="integer", example=10),
     *              @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Product created successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Product")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
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
     * @OA\Get(
     *      path="/api/products/{id}",
     *      operationId="getProductById",
     *      tags={"Products"},
     *      summary="Get product details",
     *      description="Returns product details by ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer"),
     *          description="Product ID"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Product retrieved successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Product")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found"
     *      )
     * )
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
     * @OA\Put(
     *      path="/api/products/{id}",
     *      operationId="updateProduct",
     *      tags={"Products"},
     *      summary="Update a product",
     *      description="Updates an existing product in the database.",
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer"),
     *          description="Product ID"
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"category_id", "name", "price", "quantity", "status"},
     *              @OA\Property(property="category_id", type="integer", example=1),
     *              @OA\Property(property="name", type="string", example="Updated Product Name"),
     *              @OA\Property(property="price", type="number", format="float", example=79.99),
     *              @OA\Property(property="quantity", type="integer", example=5),
     *              @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="inactive"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Product updated successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Product")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found"
     *      )
     * )
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
     * @OA\Delete(
     *      path="/api/products/{id}",
     *      operationId="deleteProduct",
     *      tags={"Products"},
     *      summary="Delete a product",
     *      description="Removes a product from the database.",
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer"),
     *          description="Product ID"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Product deleted successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Product deleted successfully.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found"
     *      )
     * )
    */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
   
        return $this->sendResponse([], 'Product deleted successfully.');
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="Product",
     *     type="object",
     *     title="Product",
     *     required={"id", "name", "price", "quantity", "category_id", "status", "user_id"},
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="Sample Product"),
     *     @OA\Property(property="price", type="number", format="float", example=100.99),
     *     @OA\Property(property="quantity", type="integer", example=10),
     *     @OA\Property(property="category_id", type="integer", example=1),
     *     @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
     *     @OA\Property(property="user_id", type="integer", example=5),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-16T12:00:00Z"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-16T12:00:00Z")
     * )
    */
    public function index()
    {
        return response()->json(Product::with('category', 'user')->get());
    }

    /**
     * @OA\Get(
     *      path="/api/products/{id}",
     *      operationId="getProductById",
     *      tags={"Products"},
     *      summary="Get a single product",
     *      description="Returns a product with category and user details",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the product",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Product")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found"
     *      )
     * )
     */
    public function show(Product $product)
    {
        return response()->json($product->load('category', 'user'));
    }

    /**
     * @OA\Post(
     *      path="/api/products",
     *      operationId="createProduct",
     *      tags={"Products"},
     *      summary="Create a new product",
     *      description="Adds a new product to the system",
     *      security={{ "bearerAuth":{} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name", "price", "quantity", "category_id", "status"},
     *              @OA\Property(property="name", type="string", example="Product A"),
     *              @OA\Property(property="price", type="number", format="float", example=99.99),
     *              @OA\Property(property="quantity", type="integer", example=10),
     *              @OA\Property(property="category_id", type="integer", example=1),
     *              @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Product created successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Product created successfully"),
     *              @OA\Property(property="product", type="object", ref="#/components/schemas/Product")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:active,inactive',
        ]);

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'category_id' => $request->category_id,
            'status' => $request->status,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    }

    /**
     * @OA\Put(
     *      path="/api/products/{id}",
     *      operationId="updateProduct",
     *      tags={"Products"},
     *      summary="Update a product",
     *      description="Updates an existing product",
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the product",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="Updated Product"),
     *              @OA\Property(property="price", type="number", format="float", example=120.99),
     *              @OA\Property(property="quantity", type="integer", example=5),
     *              @OA\Property(property="category_id", type="integer", example=2),
     *              @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="inactive"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Product updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Product updated successfully"),
     *              @OA\Property(property="product", type="object", ref="#/components/schemas/Product")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Unauthorized"
     *      )
     * )
     */
    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:products,name,' . $product->id,
            'price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:1',
            'category_id' => 'sometimes|required|exists:categories,id',
            'status' => 'sometimes|required|in:active,inactive',
        ]);

        $product->update($request->all());

        return response()->json(['message' => 'Product updated successfully', 'product' => $product]);
    }

    /**
     * @OA\Delete(
     *      path="/api/products/{id}",
     *      operationId="deleteProduct",
     *      tags={"Products"},
     *      summary="Delete a product",
     *      description="Deletes a product",
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the product",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Product deleted successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Product deleted successfully")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Unauthorized"
     *      )
     * )
     */
    public function destroy(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}

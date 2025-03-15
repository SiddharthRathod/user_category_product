<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Auth;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization logic for create/update
        if ($this->isMethod('post')) {
            return $this->user()->can('create', \App\Models\Product::class);
        }
        
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $this->user()->can('update', $this->route('product'));
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $productId = $this->route('product') ? $this->route('product')->id : null;

        return [
            'category_id' => 'required|exists:categories,id',
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('products', 'name')->ignore($productId),
            ],
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1|max:2147483647',
        ];
    }

}

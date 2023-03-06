<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;

class ProductController extends Controller
{

  public function __construct()
  {
    $this->middleware('permission:product-list|product-create|product-edit|product-delete', ['only' => ['index','show']]);
    $this->middleware('permission:product-create', ['only' => ['create','store']]);
    $this->middleware('permission:product-edit', ['only' => ['edit','update']]);
    $this->middleware('permission:product-delete', ['only' => ['destroy']]);
  }

  public function index()
  {
    $products = Product::latest()->paginate(5);
    return response()->json(['products' => $products]);
  }

  public function store(Request $request)
  {
    $this->validate($request, ['name' => 'required', 'detail'  => 'required']);
    return response()->json(['success' => true], 201);
  }

  public function show(Product $product)
  {
    return response()->json(['product' => $product]);
  }

  public function edit(Product $product)
  {
    return response()->json(['product' => $product]);
  }

  public function update(Request $request, Product $product)
  {
    $this->validate($request, [
      'name' => 'required',
      'detail' => 'required',
    ]);

    $product->update($request->all());

    return response()->json(['success' => true], 201);
  }

  public function destroy(Product $product)
  {
    $product->delete();
    return response()->json([], 204);
  }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View as FacadesView;
use Illuminate\View\View as ViewView;

class ProductController extends Controller
{
    //
    public function index() : View
    {
        //get all products
        $products = Product::latest()->paginate(10);

        //render view with all products
        return view('products.index',compact('products'));
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        //validate form
        $request->validate([
            'image' => 'required |image|mimes:png,jpg|max:2048',
            'title'=> 'required |min:5',
            'description' => 'required |min:10',
            'price' => 'required |numeric',
            'stock'=> 'required |numeric'

        ]);

        //uploud image
        $image = $request->file('image');
        $image->storeAs('public/products',$image->hashName());

        //create product
        Product::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
        ]);

        return redirect()->route('products.index')->with(['success' => "Data Anda Berhasil Disimpan"]);

    }

    public function show(string $id)
    {
        //get product by id
        $product = Product::findOrFail($id);

        return view('products.show',compact('product'));
    }
    public function edit(string $id){
        $product = Product::findOrFail($id);

        return view('products.edit', compact('product'));
    }
    public function update(Request $request, $id){
        //validate form
        $request->validate([
            'image' => 'image|mimes:png,jpg|max:2048',
            'title'=> 'required |min:5',
            'description' => 'required |min:10',
            'price' => 'required |numeric',
            'stock'=> 'required |numeric'

        ]);
        //get product by id
        $product = Product::findOrFail($id);

        //check if product is uploaded
        if ($request->hasFile('image')) {
            // Upload gambar baru
            $image = $request->file('image');
            $imagePath = $image->storeAs('public/products', $image->hashName());

            // Hapus gambar lama jika ada
            if ($product->image) {
                Storage::delete('public/products/' . $product->image);
            }

            // Update data produk dengan gambar baru
            $product->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
            ]);
        } else {
            // Update data produk tanpa mengubah gambar
            $product->update([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
            ]);
        }
        return redirect()->route('products.index')->with(['success' => 'Data Berhasil di Update']);

    }
    public function destroy($id){
        $product = Product::findOrFail($id);

        storage::delete('public/products'. $product->image);

        $product->delete();

        return redirect()->route('products.index')->with(['succuss' => 'Data Berhasil di hapus']);
    }
}

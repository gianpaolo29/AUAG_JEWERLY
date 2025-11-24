<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * List products with simple filters.
     */
    public function index(Request $request)
    {
        // 1. Get and sanitize all request inputs
        $q = $request->string('q')->toString();
        $category_id = $request->integer('category_id');
        $status = $request->string('status')->toString(); // 'active' or 'inactive'
        $stock_status = $request->string('stock_status')->toString(); // 'normal', 'low', or 'out'
        $sort = $request->string('sort', 'name')->toString(); // Default sort by name
        $dir = $request->string('dir', 'asc')->toString(); // Default direction ascending

        $lowStock = 10;

        // Ensure sortable columns are safe (prevents SQL injection)
        $allowedSorts = ['name', 'price', 'quantity', 'is_active'];
        if (! in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        // 2. Calculate Dashboard Stats
        $stats = [
            // Assuming the Product model uses 'is_active' for status, not 'status'
            'total_products' => Product::count(),
            'active_products' => Product::where('status', true)->count(),
            'low_stock' => Product::where('quantity', '=>', 0)
                ->where('quantity', '<', $lowStock)
                ->count(),
            'categories' => Category::count(),
        ];

        // 3. Build the Products Query
        $products = Product::query()
            // Eager load category and the first picture URL for the thumbnail
            ->with('category:id,name')
            ->with('pictureUrl')

            // Search filter
            ->when($q, fn ($qr) => $qr->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            }))

            // Category filter
            ->when($category_id, fn ($qr) => $qr->where('category_id', $category_id))

            // Status filter
            ->when($status === 'active', fn ($qr) => $qr->where('status', true))
            ->when($status === 'inactive', fn ($qr) => $qr->where('status', false))

            // Stock Status filter (Using the 'quantity' column as 'stock')
            ->when($stock_status === 'out', fn ($qr) => $qr->where('quantity', 0))
            ->when($stock_status === 'low', fn ($qr) => $qr->where('quantity', '>', 0)->where('quantity', '<', $lowStock))
            ->when($stock_status === 'normal', fn ($qr) => $qr->where('quantity', '>=', $lowStock))

            // Apply Sorting
            ->orderBy($sort, $dir)

            // Paginate and retain all query string parameters
            ->paginate(10)
            ->withQueryString();

        // 4. Get Categories for the Filter Dropdown
        $categories = Category::orderBy('name')->get(['id', 'name']);

        // 5. Return the view with all necessary data
        return view('admin.products.index', compact(
            'products',
            'categories',
            'stats',
            'q',
            'category_id',
            'status',
            'stock_status',
            'sort',
            'dir',
            'lowStock'
        ));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $product = new Product(['status' => true, 'quantity' => 0]);
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.products.form', compact('product', 'categories'));
    }

    /**
     * Store new product with 1 image.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'quantity' => ['required', 'integer', 'min:0'],
            'status' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'quantity' => $validated['quantity'],
            'status' => (bool) ($validated['status'] ?? true),
        ]);

        if ($request->hasFile('image')) {
            $image      = $request->file('image');
            $imageName  = time() . '_' . $image->getClientOriginalName();

            // Move to project_name/public/products
            $image->move(public_path('products'), $imageName);

            // Save URL or path in DB
            $product->pictureUrl()->create([
                'url' => 'products/' . $imageName,
            ]);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(Product $product)
    {
        $product->load('pictureUrl');
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.products.form', compact('product', 'categories'));
    }

    /**
     * Update product + replace image if uploaded.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'quantity' => ['required', 'integer', 'min:0'],
            'status' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $product->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'quantity' => $validated['quantity'],
            'status' => (bool) ($validated['status'] ?? $product->status),
        ]);

        // If new image uploaded, delete old + save new
        if ($request->hasFile('image')) {
            if ($product->pictureUrl) {
                Storage::disk('public')->delete($product->pictureUrl->url);
                $product->pictureUrl->delete();
            }

            $path = $request->file('image')->store('products', 'public');

            $product->pictureUrl()->create([
                'url' => $path,
            ]);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Delete product + its image (file + record).
     */
    public function destroy(Product $product)
    {
        if ($product->pictureUrl) {
            Storage::disk('public')->delete($product->pictureUrl->url);
            $product->pictureUrl->delete();
        }

        $product->delete();

        return back()->with('success', 'Product deleted successfully.');
    }

    /**
     * Toggle active / inactive status.
     */
    public function toggle(Product $product)
    {
        $product->update([
            'status' => ! $product->status,
        ]);

        return back()->with('success', 'Product status updated.');
    }
}

<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class StaffProductController extends Controller
{
    /**
     * Product List
     */
   public function index(Request $request)
    {
        // 1. Get and sanitize all request inputs
        $q            = $request->string('q')->toString();
        $category_id  = $request->integer('category_id');
        $status       = $request->string('status')->toString();       // 'active' or 'inactive'
        $stock_status = $request->string('stock_status')->toString(); // 'normal', 'low', or 'out'
        $sort         = $request->string('sort', 'name')->toString(); // Default sort by name
        $dir          = $request->string('dir', 'asc')->toString();   // Default direction ascending

        $lowStock = 10;

        // Ensure sortable columns are safe (prevents SQL injection)
        $allowedSorts = ['name', 'price', 'quantity', 'status'];
        if (! in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        // 2. Calculate Dashboard Stats
        $stats = [
            'total_products'  => Product::count(),
            'active_products' => Product::where('status', true)->count(),
            'low_stock'       => Product::where('quantity', '>=', 0)
                                        ->where('quantity', '<', $lowStock)
                                        ->count(),
            'categories'      => Category::count(),
        ];

        // 3. Build the Products Query
        $products = Product::query()
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

            // Stock Status filter
            ->when($stock_status === 'out', fn ($qr) => $qr->where('quantity', 0))
            ->when($stock_status === 'low', fn ($qr) => $qr->where('quantity', '>', 0)->where('quantity', '<', $lowStock))
            ->when($stock_status === 'normal', fn ($qr) => $qr->where('quantity', '>=', $lowStock))

            ->orderBy($sort, $dir)
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('staff.products.index', compact(
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
        $product    = new Product(['status' => true, 'quantity' => 0]);
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('staff.products.form', compact('product', 'categories'));
    }

    /**
     * Store new product with 1 image.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:180'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'quantity'    => ['required', 'integer', 'min:0'],
            'status'      => ['sometimes', 'boolean'],

            // new fields
            'material'    => ['nullable', 'string', Rule::in(Product::MATERIAL_OPTIONS)],
            'size'        => ['nullable', 'string', 'max:100'],
            'style'       => ['nullable', 'string', Rule::in(Product::STYLE_OPTIONS)],

            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $product = Product::create([
            'name'        => $validated['name'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'],
            'quantity'    => $validated['quantity'],
            'status'      => (bool) ($validated['status'] ?? true),

            'material'    => $validated['material'] ?? null,
            'size'        => $validated['size'] ?? null,
            'style'       => $validated['style'] ?? null,
        ]);

        // Handle one image only – save into public/products
        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();

            // physical path: public/products/xxx.jpg
            $file->move(public_path('products'), $filename);

            // DB value: "products/filename.jpg"
            $relativePath = 'products/' . $filename;

            $product->pictureUrl()->create([
                'url' => $relativePath,
            ]);
        }

        $this->notifyAdmins(new NewCustomerRegisteredNotification($user));
        return redirect()
            ->route('staff.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(Product $product)
    {
        $product->load('pictureUrl');
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('staff.products.form', compact('product', 'categories'));
    }

    /**
     * Update product + replace image if uploaded.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:180'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'quantity'    => ['required', 'integer', 'min:0'],
            'status'      => ['sometimes', 'boolean'],

            'material'    => ['nullable', 'string', Rule::in(Product::MATERIAL_OPTIONS)],
            'size'        => ['nullable', 'string', 'max:100'],
            'style'       => ['nullable', 'string', Rule::in(Product::STYLE_OPTIONS)],

            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $product->update([
            'name'        => $validated['name'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'],
            'quantity'    => $validated['quantity'],
            'status'      => (bool) ($validated['status'] ?? $product->status),

            'material'    => $validated['material'] ?? $product->material,
            'size'        => $validated['size'] ?? $product->size,
            'style'       => $validated['style'] ?? $product->style,
        ]);

        // If new image uploaded, delete old + save new in public/products
        if ($request->hasFile('image')) {
            if ($product->pictureUrl) {
                $oldPath = public_path($product->pictureUrl->url);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
                $product->pictureUrl->delete();
            }

            $file     = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('products'), $filename);

            $relativePath = 'products/' . $filename;

            $product->pictureUrl()->create([
                'url' => $relativePath,
            ]);
        }

        return redirect()
            ->route('staff.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Delete product + its image (file + record).
     */
    public function destroy(Product $product)
    {
        if ($product->pictureUrl) {
            $path = public_path($product->pictureUrl->url);

            if (File::exists($path)) {
                File::delete($path);
            }

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

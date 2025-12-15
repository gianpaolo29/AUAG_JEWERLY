@php
    use Illuminate\Support\Facades\Storage;

    $existingImage = $product->pictureUrl
    ? asset('storage/' .$product->pictureUrl->url)
    : null;
@endphp

<x-admin-layout :title="$product->exists ? 'Edit Product' : 'Add Product'">
    <div class="flex flex-col gap-6">

        {{-- Sticky header --}}
        <div
            class="sticky top-0 z-10 bg-white border-b border-gray-200 py-3 px-4 sm:px-6 -mx-4 sm:-mx-6 lg:-mx-8 flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900 ml-4 sm:ml-0">
                {{ $product->exists ? 'Edit Product' : 'Add New Product' }}
            </h1>
            <div class="flex items-center gap-3 mr-4 sm:mr-0">
                <a href="{{ route('admin.products.index') }}"
                   class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 text-sm font-medium transition">
                    Cancel
                </a>

                <button type="submit" form="product-form"
                        class="px-4 py-2 rounded-lg bg-yellow-600 text-white text-sm font-semibold hover:bg-yellow-700 shadow-md transition">
                    {{ $product->exists ? 'Save Changes' : 'Save Product' }}
                </button>
            </div>
        </div>

        <form id="product-form"
              x-data="previewUploader('{{ $existingImage }}')"
              method="POST"
              enctype="multipart/form-data"
              action="{{ $product->exists
                        ? route('admin.products.update', $product)
                        : route('admin.products.store') }}"
              class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-2"
        >
            @csrf
            @if($product->exists)
                @method('PUT')
            @endif

            {{-- LEFT: basic info + pricing --}}
            <div class="lg:col-span-2 flex flex-col gap-6">
                {{-- Basic info --}}
                <div class="bg-white rounded-xl shadow-sm p-6 space-y-5 border-t-4 border-yellow-600">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-3 -mx-6 px-6">
                        Basic Information
                    </h3>

                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700">
                            Product Name <span class="text-rose-600">*</span>
                        </label>
                        <input id="name" type="text" name="name"
                               value="{{ old('name', $product->name) }}"
                               required
                               placeholder="Enter product name"
                               class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-yellow-500 focus:border-yellow-500"/>
                        @error('name')
                        <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Category --}}
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-gray-700">
                            Category <span class="text-rose-600">*</span>
                        </label>
                        <select id="category_id" name="category_id" required
                                class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-yellow-500 focus:border-yellow-500">
                            <option value="">Select Category</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}"
                                    @selected(old('category_id', $product->category_id) == $c->id)>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                        <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="4"
                                placeholder="Describe the product..."
                                class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-yellow-500 focus:border-yellow-500">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                        <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Material --}}
                    <div>
                        <label for="material" class="block text-sm font-semibold text-gray-700">
                            Material
                        </label>
                        <select id="material" name="material"
                                class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-yellow-500 focus:border-yellow-500">
                            <option value="">Select Material</option>
                            @foreach(\App\Models\Product::MATERIAL_OPTIONS as $mat)
                                <option value="{{ $mat }}" @selected(old('material', $product->material) === $mat)>
                                    {{ ucfirst($mat) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Size --}}
                    <div>
                        <label for="size" class="block text-sm font-semibold text-gray-700">
                            Size
                        </label>
                        <input type="text" id="size" name="size"
                            value="{{ old('size', $product->size) }}"
                            placeholder="e.g. 18 inches, Adjustable, 2mm, Medium"
                            class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-yellow-500 focus:border-yellow-500">
                    </div>

                    {{-- Style --}}
                    <div>
                        <label for="style" class="block text-sm font-semibold text-gray-700">
                            Style
                        </label>
                        <select id="style" name="style"
                                class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-yellow-500 focus:border-yellow-500">
                            <option value="">Select Style</option>
                            @foreach(\App\Models\Product::STYLE_OPTIONS as $sty)
                                <option value="{{ $sty }}" @selected(old('style', $product->style) === $sty)>
                                    {{ ucfirst($sty) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                {{-- Pricing & stock --}}
                <div class="bg-white rounded-xl shadow-sm p-6 space-y-5 border-t-4 border-yellow-600">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-3 -mx-6 px-6">
                        Pricing & Inventory
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- Price --}}
                        <div>
                            <label for="price" class="block text-sm font-semibold text-gray-700">
                                Price <span class="text-rose-600">*</span>
                            </label>
                            <div class="mt-1 relative rounded-lg shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">₱</span>
                                </div>
                                <input id="price" name="price" type="number" step="0.01" min="0"
                                       value="{{ old('price', $product->price) }}"
                                       required
                                       placeholder="0.00"
                                       class="block w-full pl-7 pr-3 rounded-lg border-gray-300 text-sm focus:ring-yellow-500 focus:border-yellow-500"/>
                            </div>
                            @error('price')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Quantity --}}
                        <div>
                            <label for="quantity" class="block text-sm font-semibold text-gray-700">
                                Quantity <span class="text-rose-600">*</span>
                            </label>
                            <input id="quantity" name="quantity" type="number" min="0"
                                   value="{{ old('quantity', $product->quantity ?? 0) }}"
                                   required
                                   class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-yellow-500 focus:border-yellow-500"/>
                            @error('quantity')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="flex items-center gap-3 mt-5 sm:mt-7">
                            <label for="status" class="text-sm font-semibold text-gray-700">
                                Active
                            </label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input id="status" name="status" type="checkbox" value="1"
                                       class="sr-only peer"
                                       @checked(old('status', $product->status ?? true))>
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer
                                           peer-checked:after:translate-x-full peer-checked:after:border-white
                                           after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                           after:bg-white after:border-gray-300 after:border after:rounded-full
                                           after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Image --}}
            <div class="lg:col-span-1 flex flex-col gap-6">
                <div class="bg-white rounded-xl shadow-sm p-6 space-y-4 border-t-4 border-yellow-600">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-3 -mx-6 px-6">
                        Product Image
                    </h3>

                    <label for="image_upload"
                           class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:border-yellow-400 transition">
                        <svg class="w-8 h-8 text-yellow-600 mb-2" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <path
                                d="M4 14.899L7.5 11.399L10.5 14.399L16 8.899M16 8.899H13.5M16 8.899V11.399M21 15V9C21 8.44772 20.5523 8 20 8H13L10.5 5.5H4C3.44772 5.5 3 5.94772 3 6.5V17.5C3 18.0523 3.44772 18.5 4 18.5H20C20.5523 18.5 21 18.0523 21 17.5V15Z"
                                stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <p class="text-xs text-gray-600">
                            Click to upload (JPG, PNG, WEBP – Max 2MB)
                        </p>
                        <input id="image_upload" type="file" name="image" accept="image/*"
                               @change="onFile"
                               class="hidden" {{ $product->exists ? '' : '' }}/>
                    </label>

                    @error('image')
                    <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center justify-center mt-3">
                        <div
                            class="h-48 w-48 rounded-xl bg-gray-100 overflow-hidden grid place-content-center border border-gray-300 shadow-sm">
                            <template x-if="preview">
                                <img :src="preview" class="h-48 w-48 object-cover" alt="preview">
                            </template>
                            <template x-if="!preview">
                                <svg class="h-10 w-10 text-gray-400" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.6">
                                    <path d="M4 5h16v14H4zM4 15l4-4 4 4 4-3 4 3" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script>
        function previewUploader(existingImage) {
            return {
                preview: existingImage || null,

                onFile(e) {
                    const f = e.target.files[0];
                    if (!f) return;
                    this.preview = URL.createObjectURL(f);
                }
            }
        }
        </script>
    </div>
</x-admin-layout>

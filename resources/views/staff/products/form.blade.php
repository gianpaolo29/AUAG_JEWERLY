@php
    use Illuminate\Support\Facades\Storage;

    $existingImage = $product->pictureUrl
        ? Storage::url($product->pictureUrl->url)
        : null;

    $imageRequired = !$product->exists || !$existingImage;
@endphp

<x-staff-layout :navItems="$navItems ?? []">

    {{-- Main content container --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Sticky header --}}
        <div
            class="sticky top-0 z-20 bg-gray-50/95 backdrop-blur border-b border-gray-200 py-3 
                   -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 
                   flex items-center justify-between shadow-sm">
             
            <h1 class="text-xl font-bold text-gray-900">
                {{ $product->exists ? 'Edit Product' : 'Add New Product' }}
            </h1>

            <div class="flex items-center gap-3">
                <a href="{{ route('staff.products.index') }}"
                   class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-200 text-sm font-medium transition">
                    Cancel
                </a>

                <button type="submit" form="product-form"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold 
                               hover:bg-indigo-700 shadow-md transition">
                    {{ $product->exists ? 'Save Changes' : 'Save Product' }}
                </button>
            </div>
        </div>

        {{-- Form Start --}}
        <form id="product-form"
              x-data="{
                preview: @js($existingImage),
                isCreating: @js(!$product->exists),
                onFile(e) {
                    const [f] = e.target.files || [];
                    if (!f) {
                        this.preview = this.isCreating ? null : @js($existingImage);
                        return;
                    }
                    this.preview = URL.createObjectURL(f);
                }
            }"
              method="POST"
              enctype="multipart/form-data"
              action="{{ $product->exists ? route('staff.products.update', $product) : route('staff.products.store') }}"
              class="pt-6">
             
            @csrf

            @if($product->exists)
                @method('PUT')
            @endif

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 text-red-800 border border-red-200 rounded-xl">
                    <p class="font-semibold mb-2">Please fix the following errors:</p>
                    <ul class="list-disc ml-5 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- SINGLE CARD CONTAINER --}}
            <div class="bg-white rounded-2xl shadow-xl p-6 lg:p-8 border-t-4 border-indigo-600 ring-1 ring-indigo-200">
                
                <h2 class="text-3xl font-extrabold text-gray-900 mb-6">Product Details</h2>
                
                {{-- Main Content Grid: 3/5 width for fields (Left) | 2/5 width for image (Right) --}}
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                    
                    {{-- LEFT SIDE: ALL INPUTS (3/5 width) --}}
                    <div class="lg:col-span-3 space-y-6">
                        
                        {{-- Basic Info Section --}}
                        <div class="border-b pb-4">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Basic Information</h3>
                            <div class="space-y-4">
                                {{-- Name --}}
                                <div>
                                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">
                                        Product Name <span class="text-rose-600">*</span>
                                    </label>
                                    <input id="name" type="text" name="name"
                                           value="{{ old('name', $product->name) }}"
                                           required
                                           placeholder="Enter product name"
                                           class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                                    @error('name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Category --}}
                                <div>
                                    <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-1">
                                        Category <span class="text-rose-600">*</span>
                                    </label>
                                    <select name="category_id" id="category_id" required
                                            class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $c)
                                            <option value="{{ $c->id }}"
                                                    @selected(old('category_id', $product->category_id) == $c->id)>
                                                {{ $c->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                
                                {{-- Description --}}
                                <div>
                                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">
                                        Description
                                    </label>
                                    <textarea name="description" id="description" rows="4"
                                              placeholder="Describe the product..."
                                              class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $product->description) }}</textarea>
                                    @error('description') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Specifications Section --}}
                        <div class="border-b pb-4">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Specifications</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                                {{-- Material --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Material <span class="text-rose-600">*</span></label>
                                    <select name="material" required
                                            class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        @foreach(\App\Models\Product::MATERIAL_OPTIONS as $m)
                                            <option value="{{ $m }}" @selected(old('material', $product->material) == $m)>
                                                {{ ucfirst($m) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('material') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Size --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Size</label>
                                    <input type="text" name="size"
                                            value="{{ old('size', $product->size) }}"
                                            placeholder="e.g. 18cm, 7in"
                                            class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                                    @error('size') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Style --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Style <span class="text-rose-600">*</span></label>
                                    <select name="style" required
                                            class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        @foreach(\App\Models\Product::STYLE_OPTIONS as $s)
                                            <option value="{{ $s }}" @selected(old('style', $product->style) == $s)>
                                                {{ ucfirst($s) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('style') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Pricing & Inventory Section --}}
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Pricing & Inventory</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                                {{-- Price --}}
                                <div>
                                    <label for="price" class="block text-sm font-semibold text-gray-700 mb-1">Price <span class="text-rose-600">*</span></label>
                                    <div class="mt-1 relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">₱</span>
                                        </div>
                                        <input id="price" name="price" type="number" step="0.01" min="0" required
                                                value="{{ old('price', $product->price) }}"
                                                placeholder="0.00"
                                                class="pl-7 pr-3 w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                                    </div>
                                    @error('price') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Quantity --}}
                                <div>
                                    <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-1">Quantity <span class="text-rose-600">*</span></label>
                                    <input id="quantity" name="quantity" type="number" min="0" required
                                            value="{{ old('quantity', $product->quantity ?? 0) }}"
                                            class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                                    @error('quantity') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Status --}}
                                <div class="flex items-center gap-3 pt-6 sm:pt-0">
                                    <label for="status" class="text-sm font-semibold text-gray-700">Active</label>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input id="status" name="status" type="checkbox" value="1"
                                                class="sr-only peer"
                                                @checked(old('status', $product->status ?? true))>
                                        <div
                                            class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer
                                                    peer-checked:bg-green-500
                                                    after:content-[''] after:absolute after:top-[3px] after:left-[3px]
                                                    after:bg-white after:border after:rounded-full
                                                    after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full">
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT SIDE: IMAGE UPLOADER (2/5 width) --}}
                    <div class="lg:col-span-2 space-y-4">
                        <h3 class="text-xl font-bold text-gray-900 mb-4 border-b pb-2">Product Image</h3>

                        {{-- Image Preview Container --}}
                        <div class="flex items-center justify-center">
                            <div class="w-full max-w-xs rounded-xl bg-gray-100 overflow-hidden grid place-content-center border border-dashed border-gray-300 h-64 shadow-inner">

                                <template x-if="preview">
                                    <img :src="preview" class="w-full h-full object-cover" alt="preview">
                                </template>

                                <template x-if="!preview">
                                    <div class="flex flex-col items-center justify-center p-4">
                                        <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor"
                                             stroke-width="1.6" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4zM4 15l4-4 4 4 4-3 4 3"/>
                                        </svg>
                                        <p class="text-xs text-gray-500">No Image Selected</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Image Upload Input --}}
                        <div>
                            <label for="image_upload"
                                   class="block w-full text-center px-4 py-3 rounded-xl border border-dashed border-indigo-300 bg-indigo-50 text-sm font-medium text-indigo-700 cursor-pointer 
                                          hover:bg-indigo-100 transition-colors">
                                <span class="inline-flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    Upload Photo (JPG, PNG, WEBP)
                                </span>
                            </label>
                            
                            <input id="image_upload" type="file" name="image" accept="image/*" @change="onFile" 
                                   class="hidden" @if($imageRequired) required @endif>
                            
                            @error('image') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <p class="text-xs text-gray-500 text-center">
                            {{ $imageRequired ? 'A primary image is required to save the product.' : 'Upload a new image to replace the current one.' }}
                        </p>
                    </div>
                </div>

            </div>
            {{-- END SINGLE CARD CONTAINER --}}

        </form>

    </div>

</x-staff-layout>
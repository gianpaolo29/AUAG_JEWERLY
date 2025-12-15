<x-app-layout>
    {{-- HEADER --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Market Dashboard') }}
        </h2>
    </x-slot>

    {{-- PAGE CONTENT --}}
    <div class="py-12 bg-gray-50">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            {{-- WIDGET CARD --}}
            <div class="flex justify-center">
                <div 
                    x-data="goldSpotWidget({ endpoint: '{{ route('gold.spot') }}' })"
                    x-init="init()"
                    class="w-full max-w-lg bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-100"
                >
                    {{-- Card Header --}}
                    <div class="bg-gradient-to-r from-gray-900 to-gray-800 px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="bg-amber-500 rounded-full p-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white tracking-wide">
                                Gold Spot Price
                            </h3>
                        </div>
                        
                        {{-- Live Indicator --}}
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                            <span class="text-xs font-bold text-green-400 uppercase tracking-wider">LIVE</span>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-8 text-center">
                        
                        {{-- Loading State (Only shows on first load) --}}
                        <div x-show="loading && !formattedPhpPerOz" class="py-10">
                            <div class="animate-pulse flex flex-col items-center">
                                <div class="h-8 w-48 bg-gray-200 rounded mb-4"></div>
                                <div class="h-4 w-32 bg-gray-200 rounded"></div>
                            </div>
                        </div>

                        {{-- Error State --}}
                        <div x-show="error" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm" style="display: none;">
                            <span x-text="error"></span>
                        </div>

                        {{-- Data State --}}
                        <div x-show="formattedPhpPerOz" class="space-y-6" style="display: none;">
                            
                            {{-- Main PHP Price --}}
                            <div>
                                <div class="text-sm text-gray-500 font-medium uppercase tracking-widest mb-1">Price per Ounce (PHP)</div>
                                <div class="text-5xl font-extrabold text-gray-900 tracking-tight">
                                    <span x-text="formattedPhpPerOz"></span>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 my-4"></div>

                            {{-- Secondary USD Price --}}
                            <div class="flex items-center justify-center gap-2 text-gray-600 bg-gray-50 py-2 rounded-lg mx-auto w-fit px-4">
                                <span class="text-sm font-medium">USD Equivalent:</span>
                                <span class="text-lg font-bold text-gray-800" x-text="formattedUsdPerOz"></span>
                                <span class="text-xs text-gray-500">/ oz</span>
                            </div>

                            {{-- Footer Info --}}
                            <div class="flex justify-between items-end text-xs text-gray-400 mt-6">
                                <div class="text-left">
                                    Source: GoldAPI.io
                                </div>
                                <div class="text-right">
                                    Last update: <span x-text="lastUpdated" class="font-mono text-gray-500"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Progress Bar (Visual flair for refresh) --}}
                    <div class="h-1 w-full bg-gray-100">
                        <div class="h-full bg-amber-500 animate-[pulse_1s_cubic-bezier(0.4,0,0.6,1)_infinite]"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ALPINE COMPONENT SCRIPT --}}
    <script>
        function goldSpotWidget({ endpoint }) {
            return {
                endpoint,
                loading: true,
                error: null,
                usd_per_ounce: null,
                php_per_ounce: null,
                formattedPhpPerOz: '',
                formattedUsdPerOz: '',
                lastUpdated: null,

                async fetchPrice() {
                    // Do NOT set loading = true here to avoid flickering UI on every refresh
                    this.error = null;

                    try {
                        const res = await fetch(this.endpoint, {
                            headers: { 'Accept': 'application/json' },
                        });

                        const data = await res.json();

                        if (!res.ok || data.error) {
                            throw new Error(data.message || data.error || 'Failed to load price');
                        }

                        this.usd_per_ounce = data.usd_per_ounce;
                        this.php_per_ounce = data.php_per_ounce;

                        this.formattedPhpPerOz = new Intl.NumberFormat('en-PH', {
                            style: 'currency',
                            currency: 'PHP',
                            minimumFractionDigits: 2
                        }).format(this.php_per_ounce);

                        this.formattedUsdPerOz = new Intl.NumberFormat('en-US', {
                            style: 'currency',
                            currency: 'USD',
                            minimumFractionDigits: 2
                        }).format(this.usd_per_ounce);

                        this.lastUpdated = new Date().toLocaleTimeString('en-PH', {
                            hour12: false,
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                        });
                    } catch (e) {
                        console.error(e);
                        // Only show error on UI if we have NO data at all
                        if(!this.formattedPhpPerOz) {
                            this.error = 'Connection lost. Retrying...';
                        }
                    } finally {
                        this.loading = false;
                    }
                },

                init() {
                    this.fetchPrice();

                    // Auto-refresh every 1 second (1000ms)
                    setInterval(() => {
                        this.fetchPrice();
                    }, 600000);
                }
            }
        }
    </script>
</x-app-layout>
<x-layouts.app :title="__('AppStore')">

<div class="container mx-auto p-6">
    <header class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold">App Store</h1>
            <p class="text-zinc-400">Discover and install applications for your homelab</p>
        </div>
    </header>

    <div class="mb-6">
        <div class="relative">
            <label for="search-input"></label><input type="text" id="search-input" placeholder="Search apps..."
                                                     class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-3 pl-10 focus:border-zinc-500 focus:outline-none">
            <div class="absolute left-3 top-3">
                <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-4">Categories</h2>
        <div class="flex flex-wrap gap-3" id="categories">
            <button class="bg-zinc-600 hover:bg-zinc-700 px-4 py-2 rounded transition category-btn" data-category="all">All</button>
            <button class="bg-zinc-700 hover:bg-zinc-600 px-4 py-2 rounded transition category-btn" data-category="media">Media</button>
            <button class="bg-zinc-700 hover:bg-zinc-600 px-4 py-2 rounded transition category-btn" data-category="development">Development</button>
            <button class="bg-zinc-700 hover:bg-zinc-600 px-4 py-2 rounded transition category-btn" data-category="networking">Networking</button>
            <button class="bg-zinc-700 hover:bg-zinc-600 px-4 py-2 rounded transition category-btn" data-category="productivity">Productivity</button>
            <button class="bg-zinc-700 hover:bg-zinc-600 px-4 py-2 rounded transition category-btn" data-category="security">Security</button>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-4">Featured Apps</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="featured-apps">
        </div>
    </div>

    <div>
        <h2 class="text-2xl font-bold mb-4">All Apps</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="all-apps">
        </div>
    </div>
</div>

<div id="app-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-zinc-800 p-6 rounded-lg max-w-lg w-full mx-4">
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-2xl font-bold" id="modal-title">App Name</h3>
            <button id="close-modal" class="text-zinc-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="modal-content">
        </div>
    </div>
</div>
</x-layouts.app>

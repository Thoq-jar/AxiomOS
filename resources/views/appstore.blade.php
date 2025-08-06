<x-layouts.app :title="__('AppStore')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container mx-auto p-6">
        <header class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold">App Store</h1>
                <p class="text-zinc-400">Discover and install applications for your homelab</p>
            </div>
        </header>

        <div class="mb-6">
            <!-- Search apps -->
            <div class="relative">
                <label for="search-input"></label><input type="text" id="search-input" placeholder="Search apps..."
                                                         class="w-[40vw] bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-3 pl-10 focus:border-zinc-500 focus:outline-none">
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

    <div id="app-modal" class="fixed inset-0 backdrop-blur-xs bg-black/30 hidden items-center justify-center z-50">
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

    <script>
        console.log('JavaScript loaded');

        let allApps = {};
        let currentCategory = 'all';

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, fetching apps...');
            fetchApps();
            fetchCategories();
            setupEventListeners();
        });

        async function fetchApps() {
            try {
                console.log('Fetching apps from /api/apps');
                const response = await fetch('/api/apps');
                console.log('Response status:', response.status);
                allApps = await response.json();
                console.log('Apps data:', allApps);
                renderApps();
                renderFeaturedApps();
            } catch (error) {
                console.error('Error fetching apps:', error);
            }
        }

        async function fetchCategories() {
            try {
                const response = await fetch('/api/apps/categories');
                const categories = await response.json();
                console.log('Categories:', categories);
                renderCategories(categories);
            } catch (error) {
                console.error('Error fetching categories:', error);
            }
        }

        function renderCategories(categories) {
            const categoriesContainer = document.getElementById('categories');
            const allButton = categoriesContainer.querySelector('[data-category="all"]');

            categories.forEach(category => {
                if (!categoriesContainer.querySelector(`[data-category="${category}"]`)) {
                    const button = document.createElement('button');
                    button.className = 'bg-zinc-700 hover:bg-zinc-600 px-4 py-2 rounded transition category-btn';
                    button.setAttribute('data-category', category);
                    button.textContent = category.charAt(0).toUpperCase() + category.slice(1);
                    categoriesContainer.appendChild(button);
                }
            });
        }

        function renderApps() {
            console.log('Rendering apps, current category:', currentCategory);
            const filteredApps = currentCategory === 'all'
                ? allApps
                : Object.fromEntries(Object.entries(allApps).filter(([_, app]) => app.category === currentCategory));

            console.log('Filtered apps:', filteredApps);
            const allAppsContainer = document.getElementById('all-apps');
            allAppsContainer.innerHTML = '';

            Object.entries(filteredApps).forEach(([appId, app]) => {
                console.log('Creating card for app:', appId, app);
                const appCard = createAppCard(appId, app);
                allAppsContainer.appendChild(appCard);
            });
        }

        function renderFeaturedApps() {
            const featuredApps = Object.fromEntries(
                Object.entries(allApps).filter(([_, app]) => app.featured)
            );

            console.log('Featured apps:', featuredApps);
            const featuredContainer = document.getElementById('featured-apps');
            featuredContainer.innerHTML = '';

            Object.entries(featuredApps).forEach(([appId, app]) => {
                const appCard = createAppCard(appId, app);
                featuredContainer.appendChild(appCard);
            });
        }

        function createAppCard(appId, app) {
            const div = document.createElement('div');
            div.className = 'bg-zinc-800 p-6 rounded-lg border border-zinc-700 hover:border-zinc-600 transition';
            div.innerHTML = `
                <div class="text-4xl mb-4">${app.icon || '📦'}</div>
                <h3 class="text-xl font-bold mb-2">${app.name}</h3>
                <p class="text-zinc-400 mb-4">${app.description}</p>
                <div class="flex justify-between items-center">
                    <span class="bg-zinc-700 px-3 py-1 rounded text-sm">${app.category}</span>
                    <button onclick="showAppModal('${appId}')" class="bg-zinc-600 hover:bg-zinc-700 px-4 py-2 rounded transition">
                        View Details
                    </button>
                </div>
            `;
            return div;
        }

        function showAppModal(appId) {
            const app = allApps[appId];
            if (!app) return;

            const modal = document.getElementById('app-modal');
            const title = document.getElementById('modal-title');
            const content = document.getElementById('modal-content');

            title.textContent = app.name;
            content.innerHTML = `
                <div class="text-4xl mb-4">${app.icon || '📦'}</div>
                <p class="text-zinc-300 mb-4">${app.description}</p>
                <div class="mb-4">
                    <h4 class="font-bold mb-2">Category:</h4>
                    <span class="bg-zinc-700 px-3 py-1 rounded text-sm">${app.category}</span>
                </div>
                <div class="mb-4">
                    <h4 class="font-bold mb-2">Installation Commands:</h4>
                    <div class="bg-zinc-900 p-3 rounded text-sm font-mono max-h-40 overflow-y-auto">
                        ${app.commands.map((cmd, index) => `<div class="mb-1">${index + 1}. ${cmd}</div>`).join('')}
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="installApp('${appId}')" class="bg-zinc-600 hover:bg-zinc-700 px-4 py-2 rounded transition">
                        Install
                    </button>
                    <button onclick="closeModal()" class="bg-zinc-600 hover:bg-zinc-700 px-4 py-2 rounded transition">
                        Cancel
                    </button>
                </div>
            `;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        async function installApp(appId) {
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = 'Installing...';
            button.disabled = true;

            try {
                const response = await fetch(`/api/apps/${appId}/install`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    alert('App installed successfully!');
                    closeModal();
                } else {
                    alert('Installation failed: ' + result.error);
                }
            } catch (error) {
                console.error('Installation error:', error);
                alert('Installation failed: ' + error.message);
            } finally {
                button.textContent = originalText;
                button.disabled = false;
            }
        }

        function closeModal() {
            const modal = document.getElementById('app-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function setupEventListeners() {
            document.getElementById('close-modal').addEventListener('click', closeModal);

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('category-btn')) {
                    document.querySelectorAll('.category-btn').forEach(b => {
                        b.classList.remove('bg-zinc-600');
                        b.classList.add('bg-zinc-700');
                    });

                    e.target.classList.add('bg-zinc-600');
                    e.target.classList.remove('bg-zinc-700');

                    currentCategory = e.target.getAttribute('data-category');
                    renderApps();
                }
            });

            document.getElementById('search-input').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const filtered = Object.fromEntries(
                    Object.entries(allApps).filter(([_, app]) =>
                        app.name.toLowerCase().includes(searchTerm) ||
                        app.description.toLowerCase().includes(searchTerm) ||
                        app.category.toLowerCase().includes(searchTerm)
                    )
                );

                const allAppsContainer = document.getElementById('all-apps');
                allAppsContainer.innerHTML = '';

                Object.entries(filtered).forEach(([appId, app]) => {
                    const appCard = createAppCard(appId, app);
                    allAppsContainer.appendChild(appCard);
                });
            });
        }
    </script>
</x-layouts.app>

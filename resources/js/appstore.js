let allApps = {};
let currentCategory = 'all';

document.addEventListener('DOMContentLoaded', function() {
    fetchApps();
    fetchCategories();
    setupEventListeners();
});

async function fetchApps() {
    try {
        const response = await fetch('/api/apps');
        allApps = await response.json();
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
    const filteredApps = currentCategory === 'all'
        ? allApps
        : Object.fromEntries(Object.entries(allApps).filter(([_, app]) => app.category === currentCategory));

    const allAppsContainer = document.getElementById('all-apps');
    allAppsContainer.innerHTML = '';

    Object.entries(filteredApps).forEach(([appId, app]) => {
        const appCard = createAppCard(appId, app);
        allAppsContainer.appendChild(appCard);
    });
}

function renderFeaturedApps() {
    const featuredApps = Object.fromEntries(
        Object.entries(allApps).filter(([_, app]) => app.featured)
    );

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
            <button onclick="showAppModal('${appId}')" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded transition">
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
            <button onclick="installApp('${appId}')" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded transition">
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

let allApps = [];
let currentCategory = 'all';

document.addEventListener('DOMContentLoaded', function() {
    fetchApps();
    fetchFeaturedApps();
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('search-input').addEventListener('input', handleSearch);

    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const category = this.dataset.category;
            selectCategory(category);
        });
    });

    document.getElementById('close-modal').addEventListener('click', closeModal);
    document.getElementById('app-modal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
}

async function fetchApps() {
    try {
        const response = await fetch('/api/apps');
        allApps = await response.json();
        renderApps(allApps);
    } catch (error) {
        console.error('Error fetching apps:', error);
    }
}

async function fetchFeaturedApps() {
    try {
        const response = await fetch('/api/apps/featured');
        const apps = await response.json();
        renderFeaturedApps(apps);
    } catch (error) {
        console.error('Error fetching featured apps:', error);
    }
}

function renderFeaturedApps(apps) {
    const container = document.getElementById('featured-apps');
    container.innerHTML = apps.map(app => createAppCard(app, true)).join('');
}

function renderApps(apps) {
    const container = document.getElementById('all-apps');
    container.innerHTML = apps.map(app => createAppCard(app)).join('');
}

function createAppCard(app, featured = false) {
    const statusClass = app.installed ? 'bg-green-600' : 'bg-blue-600';
    const statusText = app.installed ? 'Installed' : 'Install';

    return `
        <div class="bg-gray-800 p-6 rounded-lg hover:bg-gray-750 transition cursor-pointer" onclick="openAppModal(${app.id})">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-gray-700 rounded-lg flex items-center justify-center mr-4">
                    ${app.icon ? `<img src="${app.icon}" class="w-8 h-8">` : '<div class="w-8 h-8 bg-blue-500 rounded"></div>'}
                </div>
                <div>
                    <h3 class="font-bold text-lg">${app.name}</h3>
                    <p class="text-gray-400 text-sm">${app.category}</p>
                </div>
            </div>
            <p class="text-gray-300 mb-4 line-clamp-3">${app.description}</p>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-400">v${app.version}</span>
                <button class="${statusClass} hover:opacity-80 px-4 py-2 rounded text-sm transition"
                        onclick="event.stopPropagation(); ${app.installed ? 'uninstallApp' : 'installApp'}(${app.id})">
                    ${statusText}
                </button>
            </div>
        </div>
    `;
}

function selectCategory(category) {
    currentCategory = category;

    document.querySelectorAll('.category-btn').forEach(btn => {
        if (btn.dataset.category === category) {
            btn.classList.remove('bg-gray-700', 'hover:bg-gray-600');
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        } else {
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btn.classList.add('bg-gray-700', 'hover:bg-gray-600');
        }
    });

    if (category === 'all') {
        renderApps(allApps);
    } else {
        const filteredApps = allApps.filter(app => app.category === category);
        renderApps(filteredApps);
    }
}

async function handleSearch(e) {
    const query = e.target.value;

    if (query.length > 2) {
        try {
            const response = await fetch(`/api/apps/search?q=${encodeURIComponent(query)}`);
            const apps = await response.json();
            renderApps(apps);
        } catch (error) {
            console.error('Error searching apps:', error);
        }
    } else if (query.length === 0) {
        if (currentCategory === 'all') {
            renderApps(allApps);
        } else {
            selectCategory(currentCategory);
        }
    }
}

async function openAppModal(appId) {
    try {
        const response = await fetch(`/api/apps/${appId}`);
        const app = await response.json();

        document.getElementById('modal-title').textContent = app.name;
        document.getElementById('modal-content').innerHTML = `
            <div class="mb-4">
                <p class="text-gray-300 mb-2">${app.description}</p>
                <div class="flex items-center gap-4 text-sm text-gray-400">
                    <span>Version: ${app.version}</span>
                    <span>Category: ${app.category}</span>
                </div>
            </div>
            <div class="flex gap-3">
                <button class="${app.installed ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'} px-6 py-2 rounded transition"
                        onclick="${app.installed ? 'uninstallApp' : 'installApp'}(${app.id})">
                    ${app.installed ? 'Uninstall' : 'Install'}
                </button>
                <button class="bg-gray-600 hover:bg-gray-700 px-6 py-2 rounded transition" onclick="closeModal()">
                    Cancel
                </button>
            </div>
        `;

        document.getElementById('app-modal').classList.remove('hidden');
        document.getElementById('app-modal').classList.add('flex');
    } catch (error) {
        console.error('Error fetching app details:', error);
    }
}

function closeModal() {
    document.getElementById('app-modal').classList.add('hidden');
    document.getElementById('app-modal').classList.remove('flex');
}

async function installApp(appId) {
    try {
        const response = await fetch(`/api/apps/${appId}/install`, { method: 'POST' });
        const data = await response.json();

        if (response.ok) {
            alert('Installation started!');
            closeModal();
            fetchApps();
        } else {
            alert('Installation failed: ' + data.message);
        }
    } catch (error) {
        console.error('Error installing app:', error);
        alert('Installation failed');
    }
}

async function uninstallApp(appId) {
    if (!confirm('Are you sure you want to uninstall this app?')) return;

    try {
        const response = await fetch(`/api/apps/${appId}/uninstall`, { method: 'DELETE' });
        const data = await response.json();

        if (response.ok) {
            alert('Uninstallation started!');
            closeModal();
            fetchApps();
        } else {
            alert('Uninstallation failed: ' + data.message);
        }
    } catch (error) {
        console.error('Error uninstalling app:', error);
        alert('Uninstallation failed');
    }
}

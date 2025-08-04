<x-layouts.app :title="__('Logs')">
    <div class="container mx-auto p-6">
        <header class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold">Logs</h1>
                <p class="text-zinc-400">View server logs</p>
            </div>
            <div class="flex gap-3">
                <button id="refresh-logs" class="bg-zinc-600 hover:bg-zinc-500 p-2 px-4 rounded transition">
                    Refresh
                </button>
                <button id="clear-logs" class="bg-red-600 hover:bg-red-500 p-2 px-4 rounded transition">
                    Clear
                </button>
            </div>
        </header>

        <!-- Recent Logs Section -->
        <div class="bg-zinc-700 p-6 rounded-lg mt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Recent Logs</h3>
                <div class="text-sm text-zinc-400" id="log-count">0 entries</div>
            </div>
            <div id="log-container" class="bg-zinc-900 rounded p-4 font-mono text-sm h-96 overflow-y-auto">
                <div class="text-zinc-400">Loading...</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <div class="bg-zinc-700 p-6 rounded-lg">
                <h3 class="text-xl font-bold mb-4">Log Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span>Total Entries</span>
                        <span class="text-zinc-300" id="total-entries">0</span>
                    </div>
                </div>
            </div>

            <div class="bg-zinc-700 p-6 rounded-lg">
                <h3 class="text-xl font-bold mb-4">Log Files</h3>
                <div id="log-files" class="space-y-2">
                    <div class="text-zinc-400">Loading log files...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Log File Modal -->
    <div id="log-file-modal" class="fixed inset-0 backdrop-blur-xs bg-black/30 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-zinc-800 rounded-lg max-w-6xl w-full max-h-[90vh] flex flex-col shadow-2xl">
                <!-- Modal Header -->
                <div class="flex justify-between items-center p-6 border-b border-zinc-600">
                    <div>
                        <h2 class="text-2xl font-bold" id="modal-file-title">Log File</h2>
                        <p class="text-zinc-400" id="modal-file-info">File information</p>
                    </div>
                    <div class="flex gap-2">
                        <button id="download-log-file" class="bg-zinc-600 hover:bg-zinc-500 p-2 px-4 rounded transition">
                            Download
                        </button>
                        <button id="close-modal" class="bg-zinc-600 hover:bg-zinc-500 p-2 px-4 rounded transition">
                            Close
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 p-6 overflow-hidden">
                    <div class="mb-4 flex justify-between items-center">
                        <div class="flex gap-2">
                            <button id="modal-refresh" class="bg-zinc-600 hover:bg-zinc-500 p-1 px-3 rounded text-sm transition">
                                Refresh
                            </button>
                            <button id="modal-search-toggle" class="bg-zinc-600 hover:bg-zinc-500 p-1 px-3 rounded text-sm transition">
                                Search
                            </button>
                        </div>
                        <div class="text-sm text-zinc-400" id="modal-log-count">0 lines</div>
                    </div>

                    <!-- Search Bar -->
                    <div id="search-bar" class="mb-4 hidden">
                        <label for="search-input"></label><input type="text" id="search-input" placeholder="Search in log file..."
                                                                 class="w-full p-2 rounded bg-zinc-700 text-white border border-zinc-600">
                    </div>

                    <!-- Log Content -->
                    <div id="modal-log-content" class="bg-zinc-900 rounded p-4 font-mono text-sm h-96 overflow-y-auto">
                        <div class="text-zinc-400">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadLogs();

            document.getElementById('refresh-logs').addEventListener('click', loadLogs);
            document.getElementById('clear-logs').addEventListener('click', () => {
                if(confirm('Are you sure you want to clear all logs?')) {
                    clearLogs();
                }
            });

            document.getElementById('close-modal').addEventListener('click', closeLogModal);
            document.getElementById('modal-refresh').addEventListener('click', refreshModalContent);
            document.getElementById('modal-search-toggle').addEventListener('click', toggleSearch);
            document.getElementById('search-input').addEventListener('input', filterLogContent);

            document.getElementById('log-file-modal').addEventListener('click', (e) => {
                if (e.target.id === 'log-file-modal') {
                    closeLogModal();
                }
            });

            setInterval(loadLogs, 30000);
        });

        let currentLogFile = null;

        async function loadLogs() {
            try {
                const response = await fetch(`/api/logs`);
                const data = await response.json();

                displayLogs(data.logs);
                updateStatistics(data.statistics);
                updateLogFiles(data.files);

            } catch(error) {
                console.error('Failed to load logs:', error);
                const container = document.getElementById('log-container');
                if (container) {
                    container.innerHTML = '<div class="text-red-400">Failed to load logs</div>';
                }
            }
        }

        function displayLogs(logs) {
            const container = document.getElementById('log-container');
            const countElement = document.getElementById('log-count');

            if (!container || !countElement) {
                console.error('Log container or count element not found');
                return;
            }

            if(logs.length === 0) {
                container.innerHTML = '<div class="text-zinc-400">No logs found</div>';
                countElement.textContent = '0 entries';
                return;
            }

            countElement.textContent = `${logs.length} entries`;

            const logsHtml = logs.map(log => {
                const timestamp = new Date(log.timestamp).toLocaleString();

                return `
                    <div class="mb-2 p-2 border-b border-zinc-600 last:border-b-0">
                        <div class="flex items-start gap-3">
                            <span class="text-xs text-zinc-400 min-w-0 flex-shrink-0">${timestamp}</span>
                            <div class="flex-1 min-w-0">
                                <div class="text-white break-words">${escapeHtml(log.message)}</div>
                                ${log.context ? `<div class="text-zinc-400 text-xs mt-1 break-words">${escapeHtml(log.context)}</div>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = logsHtml;
            container.scrollTop = container.scrollHeight;
        }

        function updateStatistics(stats) {
            const totalElement = document.getElementById('total-entries');
            if (totalElement) {
                totalElement.textContent = stats.total || 0;
            }
        }

        function updateLogFiles(files) {
            const container = document.getElementById('log-files');

            if (!container) {
                console.error('Log files container not found');
                return;
            }

            if(!files || files.length === 0) {
                container.innerHTML = '<div class="text-zinc-400">No log files found</div>';
                return;
            }

            const filesHtml = files.map(file => `
                <div class="flex justify-between items-center p-3 bg-zinc-600 rounded hover:bg-zinc-500 transition cursor-pointer"
                     onclick="openLogFile('${escapeHtml(file.path)}', '${escapeHtml(file.name)}', ${file.size})">
                    <div class="flex-1">
                        <div class="text-white font-medium">${escapeHtml(file.name)}</div>
                        <div class="text-zinc-400 text-xs">${escapeHtml(file.path)}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-zinc-300 text-sm">${formatFileSize(file.size)}</div>
                        <div class="text-zinc-400 text-xs">${formatDate(file.modified)}</div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = filesHtml;
        }

        async function openLogFile(path, name, size) {
            currentLogFile = { path, name, size };

            document.getElementById('modal-file-title').textContent = name;
            document.getElementById('modal-file-info').textContent = `${path} (${formatFileSize(size)})`;
            document.getElementById('log-file-modal').classList.remove('hidden');

            await loadLogFileContent(path);
        }

        async function loadLogFileContent(path) {
            const contentContainer = document.getElementById('modal-log-content');
            const countElement = document.getElementById('modal-log-count');

            try {
                contentContainer.innerHTML = '<div class="text-zinc-400">Loading...</div>';

                // Use the existing logs API with file parameter instead of a separate endpoint
                const response = await fetch(`/api/logs?file=${encodeURIComponent(path)}`);
                const data = await response.json();

                if (data.logs && data.logs.length > 0) {
                    countElement.textContent = `${data.logs.length} entries`;

                    const contentHtml = data.logs.map((log, index) => {
                        const lineNumber = index + 1;
                        const timestamp = new Date(log.timestamp).toLocaleString();

                        return `
                            <div class="flex hover:bg-zinc-800 transition">
                                <span class="text-zinc-500 text-xs mr-4 min-w-0 flex-shrink-0 select-none">${lineNumber.toString().padStart(4, ' ')}</span>
                                <span class="text-xs text-zinc-400 mr-4 min-w-0 flex-shrink-0">${timestamp}</span>
                                <span class="flex-1 text-white break-all">${escapeHtml(log.message)}</span>
                            </div>
                        `;
                    }).join('');

                    contentContainer.innerHTML = contentHtml;
                    contentContainer.scrollTop = contentContainer.scrollHeight;
                } else {
                    contentContainer.innerHTML = '<div class="text-zinc-400">No log entries found in this file</div>';
                    countElement.textContent = '0 entries';
                }

            } catch (error) {
                console.error('Failed to load log file:', error);
                contentContainer.innerHTML = '<div class="text-red-400">Failed to load log file</div>';
                countElement.textContent = 'Error';
            }
        }

        function closeLogModal() {
            document.getElementById('log-file-modal').classList.add('hidden');
            document.getElementById('search-bar').classList.add('hidden');
            document.getElementById('search-input').value = '';
            currentLogFile = null;
        }

        async function refreshModalContent() {
            if (currentLogFile) {
                await loadLogFileContent(currentLogFile.path);
            }
        }

        function toggleSearch() {
            const searchBar = document.getElementById('search-bar');
            const searchInput = document.getElementById('search-input');

            if (searchBar.classList.contains('hidden')) {
                searchBar.classList.remove('hidden');
                searchInput.focus();
            } else {
                searchBar.classList.add('hidden');
                searchInput.value = '';
                filterLogContent();
            }
        }

        function filterLogContent() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const contentContainer = document.getElementById('modal-log-content');
            const lines = contentContainer.querySelectorAll('div.flex');

            lines.forEach(line => {
                const text = line.textContent.toLowerCase();
                if (searchTerm === '' || text.includes(searchTerm)) {
                    line.style.display = 'flex';
                } else {
                    line.style.display = 'none';
                }
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatFileSize(bytes) {
            if(bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function formatDate(timestamp) {
            return new Date(timestamp * 1000).toLocaleDateString();
        }

        async function clearLogs() {
            try {
                const response = await fetch('/api/logs', {method: 'DELETE'});
                if(response.ok) {
                    loadLogs();
                } else {
                    alert('Failed to clear logs');
                }
            } catch(error) {
                console.error('Failed to clear logs:', error);
                alert('Failed to clear logs');
            }
        }
    </script>
</x-layouts.app>

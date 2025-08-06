<x-layouts.app :title="__('Dashboard')">
    <div class="min-h-screen transition-colors duration-200">
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <header class="mb-10">
                <div class="text-center lg:text-left">
                    <h1 class="text-5xl lg:text-6xl font-bold mb-3 text-zinc-900 dark:text-white transition-colors duration-200"
                        id="greeting">
                        Welcome
                    </h1>
                    <p class="text-lg text-zinc-600 dark:text-zinc-400 transition-colors duration-200" id="datetime"></p>
                </div>

                <div class="mt-4">
                    <!-- Search // TODO: add label -->
                    <label for="search"></label>
                    <form id="search-form">
                        <input type="text" placeholder="Search the web..." id="search"
                            class="w-[50vw] bg-zinc-200 dark:bg-zinc-700 rounded-lg border border-zinc-200 dark:border-zinc-700 p-2 text-sm text-zinc-600 dark:text-zinc-400 transition-colors duration-200">
                    </form>
                </div>
            </header>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <!-- Weather -->
                <div
                    class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">Weather</h3>
                        <div
                            class="w-8 h-8 bg-purple-100 dark:bg-cyan-900 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-center mb-2">
                        <div class="text-3xl font-bold text-zinc-900 dark:text-white mr-3" id="weather-temp">--°C</div>
                        <img id="weather-icon" class="w-12 h-12" alt="Weather" src="">
                    </div>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400" id="weather-desc">Loading...</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- System Statistics -->
                <div
                    class="lg:col-span-3 bg-white dark:bg-zinc-800 p-8 rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center mb-6">
                        <div
                            class="w-10 h-10 bg-black dark:bg-white rounded-full flex items-center justify-center mr-4">
                            <svg class="w-5 h-5 text-white dark:text-black" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">System Statistics</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- CPU Usage -->
                        <div
                            class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">CPU Usage</h3>
                                <div
                                    class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-3xl font-bold text-zinc-900 dark:text-white mb-2" id="cpu-usage">0%</div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                <div class="bg-blue-500 dark:bg-blue-400 h-2 rounded-full transition-all duration-300"
                                    id="cpu-bar" style="width: 0"></div>
                            </div>
                        </div>

                        <!-- CPU Temperature -->
                        <div
                            class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">CPU Temperature</h3>
                                <div
                                    class="w-8 h-8 bg-orange-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 2a3 3 0 00-3 3v6.5a4.5 4.5 0 107 0V5a3 3 0 00-3-3zm-1 11.227A2 2 0 1011 13.227V8a1 1 0 10-2 0v5.227z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-3xl font-bold text-zinc-900 dark:text-white mb-2" id="cpu-temperature">0°C
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                <div class="bg-blue-500 dark:bg-blue-400 h-2 rounded-full transition-all duration-300"
                                    id="cpu-temp-bar" style="width: 0"></div>
                            </div>
                        </div>

                        <!-- GPU Usage -->
                        <div
                            class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">GPU Usage</h3>
                                <div
                                    class="w-8 h-8 bg-violet-100 dark:bg-violet-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-3xl font-bold text-zinc-900 dark:text-white mb-2" id="gpu-usage">0%</div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                <div class="bg-violet-500 dark:bg-violet-400 h-2 rounded-full transition-all duration-300"
                                    id="gpu-bar" style="width: 0"></div>
                            </div>
                        </div>

                        <!-- GPU Temperature -->
                        <div
                            class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">GPU Temperature</h3>
                                <div
                                    class="w-8 h-8 bg-violet-100 dark:bg-violet-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 2a3 3 0 00-3 3v6.5a4.5 4.5 0 107 0V5a3 3 0 00-3-3zm-1 11.227A2 2 0 1011 13.227V8a1 1 0 10-2 0v5.227z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-3xl font-bold text-zinc-900 dark:text-white mb-2" id="gpu-temperature">0°C
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                <div class="bg-violet-500 dark:bg-violet-400 h-2 rounded-full transition-all duration-300"
                                    id="gpu-temp-bar" style="width: 0"></div>
                            </div>
                        </div>

                        <!-- Memory Usage -->
                        <div
                            class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">Memory Usage</h3>
                                <div
                                    class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-3xl font-bold text-zinc-900 dark:text-white mb-1" id="memory-usage">0%
                            </div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-2" id="memory-details">0 GB / 0 GB
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                <div class="bg-green-500 dark:bg-green-400 h-2 rounded-full transition-all duration-300"
                                    id="memory-bar" style="width: 0"></div>
                            </div>
                        </div>

                        <!-- Power Draw -->
                        <div
                            class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">Power Consumption
                                </h3>
                                <div
                                    class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-3xl font-bold text-zinc-900 dark:text-white mb-1" id="power-draw">Loading
                            </div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-2" id="charging"></div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                <div class="bg-green-500 dark:bg-green-400 h-2 rounded-full transition-all duration-300"
                                    id="power-bar" style="width: 0"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

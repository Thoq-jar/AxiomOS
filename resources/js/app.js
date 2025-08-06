document.addEventListener('DOMContentLoaded', () => {
    setupSearch().then();
    updateDateTime().then();
    fetchSystemStats().then();
    fetchWeather().then();

    setInterval(updateDateTime, 1000);
    setInterval(fetchSystemStats, 5000);
    setInterval(fetchWeather, 300000);
});

async function setupSearch() {
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search');

    if (!searchInput)
        return;

    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const query = searchInput.value;
        if (!query)
            return;

        window.open("https://startpage.com/do/search?query=" + query, "_blank");
    });
}

async function updateDateTime() {
    const now = new Date();
    const datetime = document.getElementById('datetime');
    if (datetime === null)
        return;
    datetime.textContent = now.toLocaleString();
}

async function fetchSystemStats() {
    try {
        const response = await fetch('/api/system-stats');
        const data = await response.json();

        const greeting = document.getElementById('greeting');
        const cpuUsage = document.getElementById('cpu-usage');
        const cpuBar = document.getElementById('cpu-bar');
        const memoryUsage = document.getElementById('memory-usage');
        const memoryDetails = document.getElementById('memory-details');
        const memoryBar = document.getElementById('memory-bar');
        const cpuTemperature = document.getElementById('cpu-temperature');
        const cpuTempBar = document.getElementById('cpu-temp-bar');
        const gpuTemperature = document.getElementById('gpu-temperature');
        const gpuTempBar = document.getElementById('gpu-temp-bar');
        const gpuUsage = document.getElementById('gpu-usage');
        const gpuUsageBar = document.getElementById('gpu-bar');
        const powerDraw = document.getElementById('power-draw');
        const powerDrawBar = document.getElementById('power-bar');
        const powerCharging = document.getElementById('charging');

        if (greeting) greeting.textContent = data.greeting;
        if (cpuUsage) cpuUsage.textContent = `${data.cpu.usage}%`;
        if (cpuBar) cpuBar.style.width = `${data.cpu.usage}%`;
        if (memoryUsage) memoryUsage.textContent = `${data.memory.percentage}%`;
        if (memoryDetails) memoryDetails.textContent = `${data.memory.used} GB / ${data.memory.total} GB`;
        if (memoryBar) memoryBar.style.width = `${data.memory.percentage}%`;
        if (cpuTemperature) cpuTemperature.textContent = `${data.cpu.temperature}°C`;
        if (cpuTempBar) {
            const tempPercentage = Math.min((data.cpu.temperature / 80) * 100, 100);
            cpuTempBar.style.width = `${tempPercentage}%`;
        }
        if (gpuTemperature) gpuTemperature.textContent = `${data.gpu.temperature}°C`;
        if (gpuTempBar) {
            const tempPercentage = Math.min((data.gpu.temperature / 80) * 100, 100);
            gpuTempBar.style.width = `${tempPercentage}%`;
        }
        if (gpuUsage) {
            let usage = data.gpu.usage;
            if (!usage) usage = "Unknown";
            else usage = usage + "%";
            gpuUsage.textContent = `${usage}`;
        }
        if (gpuUsageBar) gpuUsageBar.style.width = data.gpu.usage ? `${data.gpu.usage}%` : '0%';
        if (powerDraw) powerDraw.textContent = `${data.power.power_consumption} W`;
        if (powerDrawBar) powerDrawBar.style.width = `${data.power.power_consumption || 0}%`;
        if (powerCharging) powerCharging.textContent = data.power.charging ? 'Charging' : 'Not Charging';
    } catch (error) {
        console.error('Error fetching system stats:', error);
    }
}

async function fetchWeather() {
    try {
        const response = await fetch('/api/weather');
        const data = await response.json();

        const weatherTemp = document.getElementById('weather-temp');
        const weatherDesc = document.getElementById('weather-desc');
        const weatherIcon = document.getElementById('weather-icon');

        if (!weatherTemp || !weatherDesc) return;

        if (data.error) {
            weatherTemp.textContent = '--°C';
            weatherDesc.textContent = data.error;
            return;
        }

        weatherTemp.textContent = `${data.temperature}°C`;
        weatherDesc.textContent = data.description;
        if (weatherIcon) weatherIcon.src = `https://openweathermap.org/img/w/${data.icon}.png`;

    } catch (error) {
        console.error('Error fetching weather:', error);
        const weatherDesc = document.getElementById('weather-desc');
        if (weatherDesc) weatherDesc.textContent = 'Weather unavailable';
    }
}

document.addEventListener('DOMContentLoaded', function() {

    updateDateTime();
    fetchSystemStats().then();
    fetchWeather().then();

    setInterval(updateDateTime, 1000);
    setInterval(fetchSystemStats, 5000);
    setInterval(fetchWeather, 300000);
});

function updateDateTime() {
    const now = new Date();
    const datetime = document.getElementById('datetime');
    if(datetime === null)
        return;
    datetime.textContent = now.toLocaleString();
}

async function fetchSystemStats() {
    const isDashboardPage = window.location.pathname.includes('dashboard');
    if(!isDashboardPage)
        return;

    try {
        const response = await fetch('/api/system-stats');
        const data = await response.json();

        const greeting = document.getElementById('greeting');
        const cpuUsage = document.getElementById('cpu-usage');
        const cpuBar = document.getElementById('cpu-bar');
        const memoryUsage = document.getElementById('memory-usage');
        const memoryDetails = document.getElementById('memory-details');
        const memoryBar = document.getElementById('memory-bar');
        const temperature = document.getElementById('temperature');
        const tempBar = document.getElementById('temp-bar');

        if(greeting) greeting.textContent = data.greeting;
        if(cpuUsage) cpuUsage.textContent = `${data.cpu_usage}%`;
        if(cpuBar) cpuBar.style.width = `${data.cpu_usage}%`;

        if(memoryUsage) memoryUsage.textContent = `${data.memory_usage.percentage}%`;
        if(memoryDetails) memoryDetails.textContent = `${data.memory_usage.used} GB / ${data.memory_usage.total} GB`;
        if(memoryBar) memoryBar.style.width = `${data.memory_usage.percentage}%`;

        if(temperature) temperature.textContent = `${data.temperature}°C`;
        if(tempBar) {
            const tempPercentage = Math.min((data.temperature / 80) * 100, 100);
            tempBar.style.width = `${tempPercentage}%`;
        }

    } catch(error) {
        console.error('Error fetching system stats:', error);
    }
}

async function fetchWeather() {
    const isDashboardPage = window.location.pathname.includes('dashboard');
    if(!isDashboardPage)
        return;

    try {
        const response = await fetch('/api/weather');
        const data = await response.json();

        const weatherTemp = document.getElementById('weather-temp');
        const weatherDesc = document.getElementById('weather-desc');
        const weatherIcon = document.getElementById('weather-icon');

        if(!weatherTemp || !weatherDesc) return;

        if(data.error) {
            weatherTemp.textContent = '--°C';
            weatherDesc.textContent = data.error;
            return;
        }

        weatherTemp.textContent = `${data.temperature}°C`;
        weatherDesc.textContent = data.description;
        if(weatherIcon) weatherIcon.src = `https://openweathermap.org/img/w/${data.icon}.png`;

    } catch(error) {
        console.error('Error fetching weather:', error);
        const weatherDesc = document.getElementById('weather-desc');
        if(weatherDesc) weatherDesc.textContent = 'Weather unavailable';
    }
}

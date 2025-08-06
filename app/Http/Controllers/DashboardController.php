<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function getSystemStats(): JsonResponse
    {
        $cpuStats = $this->getCpuStats();
        $memoryUsage = $this->getMemoryUsage();
        $gpuStats = $this->getGpuStats();
        $powerStats = $this->getPowerStats();
        $greeting = $this->getGreeting();

        return response()->json([
            'cpu' => $cpuStats,
            'memory' => $memoryUsage,
            'gpu' => $gpuStats,
            'power' => $powerStats,
            'greeting' => $greeting,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function getWeather(Request $request): JsonResponse
    {
        try {
            $location = $this->getLocationFromIP($request);

            if (!$location) {
                return response()->json(['error' => 'Unable to determine location'], 400);
            }

            $weatherData = $this->getOpenMeteoWeather($location['lat'], $location['lon']);

            if (!$weatherData) {
                return response()->json(['error' => 'Failed to fetch weather data'], 500);
            }

            $weatherCode = $weatherData['current']['weather_code'];
            $iconCode = $this->getWeatherIcon($weatherCode, $weatherData['current']['is_day']);

            return response()->json([
                'temperature' => round($weatherData['current']['temperature_2m']),
                'description' => $this->getWeatherDescription($weatherCode),
                'icon' => $iconCode,
                'humidity' => $weatherData['current']['relative_humidity_2m'],
                'wind_speed' => round($weatherData['current']['wind_speed_10m'], 1),
                'city' => $location['city'] ?? 'Unknown',
                'country' => $location['country'] ?? ''
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Weather service unavailable'], 500);
        }
    }

    private function getLocationFromIP(Request $request): ?array
    {
        $ip = $request->ip();

        if ($ip === '127.0.0.1' || $ip === '::1') {
            $ip = $this->getPublicIP();
        }

        if (!$ip) {
            return null;
        }

        try {
            $response = file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city,lat,lon");
            $data = json_decode($response, true);

            if ($data && $data['status'] === 'success') {
                return [
                    'lat' => $data['lat'],
                    'lon' => $data['lon'],
                    'city' => $data['city'],
                    'country' => $data['country']
                ];
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    private function getPublicIP(): ?string
    {
        try {
            $ip = file_get_contents('https://api.ipify.org');
            return trim($ip);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getOpenMeteoWeather(float $lat, float $lon): ?array
    {
        try {
            $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m,is_day&timezone=auto";

            $response = file_get_contents($url);
            return json_decode($response, true);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getWeatherIcon(int $weatherCode, int $isDay): string
    {
        $iconMap = [
            0 => $isDay ? '01d' : '01n',
            1 => $isDay ? '01d' : '01n',
            2 => $isDay ? '02d' : '02n',
            3 => $isDay ? '03d' : '03n',
            45 => '50d',
            48 => '50d',
            51 => '09d',
            53 => '09d',
            55 => '09d',
            56 => '13d',
            57 => '13d',
            61 => '10d',
            63 => '10d',
            65 => '10d',
            66 => '13d',
            67 => '13d',
            71 => '13d',
            73 => '13d',
            75 => '13d',
            77 => '13d',
            80 => '09d',
            81 => '09d',
            82 => '09d',
            85 => '13d',
            86 => '13d',
            95 => '11d',
            96 => '11d',
            99 => '11d'
        ];

        return $iconMap[$weatherCode] ?? ($isDay ? '01d' : '01n');
    }

    private function getWeatherDescription(int $weatherCode): string
    {
        $descriptions = [
            0 => 'Beautiful clear skies today!',
            1 => 'Mostly clear with a hint of clouds',
            2 => 'A mix of sun and fluffy clouds',
            3 => 'Gloomy grey skies overhead',
            45 => 'Spooky fog all around',
            48 => 'Freezing fog coating everything',
            51 => 'Just a sprinkle of rain',
            53 => 'Steady drizzle falling',
            55 => 'Heavy drizzle pouring down',
            56 => 'Watch out for icy drizzle!',
            57 => 'Thick freezing drizzle everywhere',
            61 => 'Light rain to freshen things up',
            63 => 'Steady rain falling',
            65 => 'Pouring rain - grab an umbrella!',
            66 => 'Careful - light freezing rain',
            67 => 'Dangerous heavy freezing rain',
            71 => 'Light snow falling gently',
            73 => 'Steady snowfall coming down',
            75 => 'Heavy snow blanketing everything',
            77 => 'Tiny snow grains falling',
            80 => 'Brief rain showers passing through',
            81 => 'Steady rain showers ongoing',
            82 => 'Intense rain coming down hard!',
            85 => 'Light snow showers drifting down',
            86 => 'Heavy snow showers piling up',
            95 => 'Thunder and lightning - stay safe!',
            96 => 'Thunderstorm with some hail mixed in',
            99 => 'Severe thunderstorm with large hail!'
        ];
        return $descriptions[$weatherCode] ?? 'Unknown';
    }

    private function getCpuUsage(): float
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            $usage = shell_exec('top -l 1 | grep "CPU usage" | awk \'{print $3}\' | sed \'s/%//\'');
            if ($usage && is_numeric(trim($usage))) {
                return round((float)trim($usage), 2);
            }
        } elseif (PHP_OS_FAMILY === 'Linux') {
            $usage = shell_exec('top -bn1 | grep "Cpu(s)" | awk \'{print $2}\' | sed \'s/%us,//\'');
            if ($usage && is_numeric(trim($usage))) {
                return round((float)trim($usage), 2);
            }
        }

        $load = sys_getloadavg();
        return round($load[0] * 25, 2);
    }

    private function getMemoryUsage(): array
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            return $this->getMacMemoryUsage();
        } elseif (PHP_OS_FAMILY === 'Linux') {
            return $this->getLinuxMemoryUsage();
        } else {
            return $this->getGenericMemoryUsage();
        }
    }

    private function getMacMemoryUsage(): array
    {
        $vmStat = shell_exec('vm_stat');
        $memInfo = shell_exec('sysctl -n hw.memsize');

        if (!$vmStat || !$memInfo) {
            return ['total' => 0, 'used' => 0, 'percentage' => 0];
        }

        $totalBytes = (int)$memInfo;
        $totalGB = round($totalBytes / 1024 / 1024 / 1024, 2);

        preg_match('/Pages free:\s+(\d+)/', $vmStat, $freeMatch);
        preg_match('/Pages inactive:\s+(\d+)/', $vmStat, $inactiveMatch);
        preg_match('/page size of (\d+) bytes/', $vmStat, $pageSizeMatch);

        $pageSize = isset($pageSizeMatch[1]) ? (int)$pageSizeMatch[1] : 4096;
        $freePages = isset($freeMatch[1]) ? (int)$freeMatch[1] : 0;
        $inactivePages = isset($inactiveMatch[1]) ? (int)$inactiveMatch[1] : 0;

        $freeBytes = ($freePages + $inactivePages) * $pageSize;
        $usedBytes = $totalBytes - $freeBytes;
        $usedGB = round($usedBytes / 1024 / 1024 / 1024, 2);

        $percentage = $totalBytes > 0 ? round(($usedBytes / $totalBytes) * 100, 2) : 0;

        return [
            'total' => $totalGB,
            'used' => $usedGB,
            'percentage' => $percentage
        ];
    }

    private function getLinuxMemoryUsage(): array
    {
        if (!file_exists('/proc/meminfo')) {
            return ['total' => 0, 'used' => 0, 'percentage' => 0];
        }

        $memInfo = file_get_contents('/proc/meminfo');
        preg_match('/MemTotal:\s+(\d+)/', $memInfo, $totalMatch);
        preg_match('/MemAvailable:\s+(\d+)/', $memInfo, $availableMatch);

        $total = isset($totalMatch[1]) ? (int)$totalMatch[1] : 0;
        $available = isset($availableMatch[1]) ? (int)$availableMatch[1] : 0;
        $used = $total - $available;

        return [
            'total' => round($total / 1024 / 1024, 2),
            'used' => round($used / 1024 / 1024, 2),
            'percentage' => $total > 0 ? round(($used / $total) * 100, 2) : 0
        ];
    }

    private function getCpuStats(): array
    {
        return ['temperature' => $this->getCpuTemperature(), 'usage' => $this->getCpuUsage()];
    }

    private function getGenericMemoryUsage(): array
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);

        if ($memoryLimit === '-1') {
            return ['total' => 0, 'used' => 0, 'percentage' => 0];
        }

        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        $totalGB = round($memoryLimitBytes / 1024 / 1024 / 1024, 2);
        $usedGB = round($memoryUsage / 1024 / 1024 / 1024, 2);
        $percentage = round(($memoryUsage / $memoryLimitBytes) * 100, 2);

        return [
            'total' => $totalGB,
            'used' => $usedGB,
            'percentage' => $percentage
        ];
    }

    private function convertToBytes(string $size): int
    {
        $unit = strtolower(substr($size, -1));
        $value = (int)substr($size, 0, -1);

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => (int)$size,
        };
    }

    private function getCpuTemperature(): float
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            $temp = shell_exec('sudo powermetrics --samplers smc -n 1 --hide-cpu-duty-cycle 2>/dev/null | grep "CPU die temperature" | awk \'{print $4}\'');
            if ($temp && is_numeric(trim($temp))) {
                return round((float)trim($temp), 1);
            }

            $temp = shell_exec('sudo powermetrics -n 1 --hide-cpu-duty-cycle 2>/dev/null | grep -i temperature | head -1 | awk \'{print $3}\'');
            if ($temp && is_numeric(trim($temp))) {
                return round((float)trim($temp), 1);
            }

            return mt_rand(350, 650) / 10;
        } elseif (PHP_OS_FAMILY === 'Linux') {
            $tempFile = '/sys/class/thermal/thermal_zone0/temp';
            if (file_exists($tempFile)) {
                $temp = file_get_contents($tempFile);
                return round($temp / 1000, 1);
            }
        }

        return mt_rand(350, 650) / 10;
    }

    private function getGreeting(): string
    {
        $hour = (int)shell_exec('date +%H') ?? 0;
        $user = auth()->user();
        $username = $user ? ucfirst($user->name) : 'Guest';

        if ($hour >= 5 && $hour < 12) {
            return "Good Morning, $username";
        } elseif ($hour >= 12 && $hour < 18) {
            return "Good Afternoon, $username";
        } elseif ($hour >= 18 && $hour < 21) {
            return "Good Evening, $username";
        } else {
            return "Good Night, $username";
        }
    }

    private function getGpuStats(): array
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            return $this->getMacGpuStats();
        } elseif (PHP_OS_FAMILY === 'Linux') {
            return $this->getLinuxGpuStats();
        }

        return ['temperature' => null, 'usage' => null, 'name' => null, 'memory_used' => null, 'memory_total' => null];
    }

    private function getMacGpuStats(): array
    {
        $gpuInfo = shell_exec('system_profiler SPDisplaysDataType 2>/dev/null');

        $activityMonitor = shell_exec('sudo powermetrics -n 1 --show-process-gpu 2>/dev/null | grep -A 10 "GPU"');
        $usage = $this->parseGpuUsageMac($activityMonitor ?? '');

        $istatOutput = shell_exec('which istats >/dev/null 2>&1 && istats 2>/dev/null | grep -i gpu');
        $temperature = $this->parseGpuTemperature($istatOutput ?? '');

        $gpuMemory = shell_exec('system_profiler SPDisplaysDataType 2>/dev/null | grep -i "VRAM"');
        $memoryInfo = $this->parseGpuMemoryMac($gpuMemory ?? '');

        return [
            'temperature' => $temperature,
            'usage' => $usage,
            'name' => $this->parseGpuName($gpuInfo ?? ''),
            'memory_used' => $memoryInfo['used'],
            'memory_total' => $memoryInfo['total']
        ];
    }
    private function getLinuxGpuStats(): array
    {
        $stats = [
            'temperature' => null,
            'usage' => null,
            'name' => null,
            'memory_used' => null,
            'memory_total' => null
        ];

        if ($this->commandExists('nvidia-smi')) {
            $nvidiaOutput = shell_exec('nvidia-smi --query-gpu=temperature.gpu,utilization.gpu,name,memory.used,memory.total --format=csv,noheader,nounits 2>/dev/null');
            if ($nvidiaOutput && trim($nvidiaOutput) !== '') {
                $values = array_map('trim', explode(',', trim($nvidiaOutput)));
                if (count($values) >= 5) {
                    $stats['temperature'] = is_numeric($values[0]) ? (float)$values[0] : null;
                    $stats['usage'] = is_numeric($values[1]) ? (float)$values[1] : null;
                    $stats['name'] = $values[2] !== '' ? $values[2] : null;
                    $stats['memory_used'] = is_numeric($values[3]) ? (float)$values[3] : null;
                    $stats['memory_total'] = is_numeric($values[4]) ? (float)$values[4] : null;
                    return $stats;
                }
            }
        }

        if ($this->commandExists('rocm-smi')) {
            $amdTemp = shell_exec('rocm-smi --showtemp --csv 2>/dev/null | tail -n +2 | head -1 | cut -d"," -f2');
            $amdUsage = shell_exec('rocm-smi --showuse --csv 2>/dev/null | tail -n +2 | head -1 | cut -d"," -f2');
            $amdName = shell_exec('rocm-smi --showproductname --csv 2>/dev/null | tail -n +2 | head -1 | cut -d"," -f2');
            $amdMemory = shell_exec('rocm-smi --showmemuse --csv 2>/dev/null | tail -n +2 | head -1 | cut -d"," -f2-3');

            if ($amdTemp && is_numeric(trim($amdTemp))) {
                $stats['temperature'] = (float)trim($amdTemp);
            }
            if ($amdUsage) {
                $usage = str_replace('%', '', trim($amdUsage));
                if (is_numeric($usage)) $stats['usage'] = (float)$usage;
            }
            if ($amdName && trim($amdName) !== '') {
                $stats['name'] = trim($amdName);
            }
            if ($amdMemory) {
                $memValues = explode(',', $amdMemory);
                if (count($memValues) >= 2) {
                    $used = str_replace('%', '', trim($memValues[0]));
                    $total = str_replace('%', '', trim($memValues[1]));
                    if (is_numeric($used)) $stats['memory_used'] = (float)$used;
                    if (is_numeric($total)) $stats['memory_total'] = (float)$total;
                }
            }
        }

        if ($this->commandExists('intel_gpu_top')) {
            $intelOutput = shell_exec('timeout 3s intel_gpu_top -J 2>/dev/null | head -n 10');
            if ($intelOutput) {
                $lines = explode("\n", trim($intelOutput));
                foreach ($lines as $line) {
                    if (trim($line) === '') continue;
                    $data = json_decode($line, true);
                    if (isset($data['engines']['Render/3D']['busy'])) {
                        $stats['usage'] = (float)$data['engines']['Render/3D']['busy'];
                        $stats['name'] = 'Intel GPU';
                        break;
                    }
                }
            }
        }

        if ($stats['name'] === null) {
            $lspciOutput = shell_exec('lspci | grep -i vga 2>/dev/null');
            if ($lspciOutput) {
                if (preg_match('/VGA compatible controller:\s*(.+)/', $lspciOutput, $matches)) {
                    $stats['name'] = trim($matches[1]);
                }
            }

            if ($stats['name'] === null) {
                $lspci3d = shell_exec('lspci | grep -i "3d controller" 2>/dev/null');
                if ($lspci3d && preg_match('/3D controller:\s*(.+)/', $lspci3d, $matches)) {
                    $stats['name'] = trim($matches[1]);
                }
            }
        }

        $hwmonDirs = glob('/sys/class/hwmon/hwmon*/');
        foreach ($hwmonDirs as $dir) {
            $name = @file_get_contents($dir . 'name');
            if ($name) {
                $name = trim($name);
                if (preg_match('/(radeon|amdgpu|nouveau|i915)/i', $name)) {
                    $tempFiles = glob($dir . 'temp*_input');
                    foreach ($tempFiles as $tempFile) {
                        $temp = @file_get_contents($tempFile);
                        if ($temp && is_numeric(trim($temp))) {
                            $stats['temperature'] = round((float)trim($temp) / 1000, 1);
                            break 2;
                        }
                    }
                }
            }
        }

        if ($this->commandExists('cat')) {
            $drmDirs = glob('/sys/class/drm/card*/device/');
            foreach ($drmDirs as $dir) {
                $vendorFile = $dir . 'vendor';
                $deviceFile = $dir . 'device';

                if (file_exists($vendorFile) && file_exists($deviceFile)) {
                    $vendor = trim(@file_get_contents($vendorFile));
                    $device = trim(@file_get_contents($deviceFile));

                    if ($vendor && $device && $stats['name'] === null) {
                        $pciIds = shell_exec("lspci -d {$vendor}:{$device} 2>/dev/null | head -1");
                        if ($pciIds && preg_match('/:\s*(.+)/', $pciIds, $matches)) {
                            $stats['name'] = trim($matches[1]);
                            break;
                        }
                    }
                }
            }
        }

        return $stats;
    }

    private function getLinuxPowerStats(): array
    {
        $powerSupplyPath = '/sys/class/power_supply/';

        $batteryPercentage = null;
        $charging = null;
        $powerConsumption = null;
        $timeRemaining = null;

        if (!is_dir($powerSupplyPath)) {
            return [
                'battery_percentage' => null,
                'charging' => null,
                'power_consumption' => null,
                'time_remaining' => null
            ];
        }

        $powerSupplies = scandir($powerSupplyPath);
        $batteryDevice = null;
        $acDevice = null;

        foreach ($powerSupplies as $device) {
            if ($device === '.' || $device === '..') continue;

            $devicePath = $powerSupplyPath . $device . '/';
            $typeFile = $devicePath . 'type';

            if (!file_exists($typeFile)) continue;

            $type = trim(file_get_contents($typeFile));

            if ($type === 'Battery' && !$batteryDevice) {
                $batteryDevice = $device;
            } elseif (in_array($type, ['Mains', 'ADP', 'AC']) && !$acDevice) {
                $acDevice = $device;
            }
        }

        if ($batteryDevice) {
            $batteryPath = $powerSupplyPath . $batteryDevice . '/';

            if (file_exists($batteryPath . 'capacity')) {
                $capacity = trim(file_get_contents($batteryPath . 'capacity'));
                if (is_numeric($capacity)) {
                    $batteryPercentage = (int)$capacity;
                }
            }

            if (file_exists($batteryPath . 'status')) {
                $status = trim(file_get_contents($batteryPath . 'status'));
                $charging = in_array(strtolower($status), ['charging', 'full']);
            }

            if (file_exists($batteryPath . 'power_now')) {
                $power = trim(file_get_contents($batteryPath . 'power_now'));
                if (is_numeric($power) && $power > 0) {
                    $powerConsumption = round((float)$power / 1000000, 2);
                }
            }
        }

        if ($charging === null && $acDevice) {
            $acPath = $powerSupplyPath . $acDevice . '/';
            if (file_exists($acPath . 'online')) {
                $online = trim(file_get_contents($acPath . 'online'));
                $charging = $online === '1';
            }
        }

        if ($powerConsumption === null) {
            $totalPowerConsumption = 0;
            $powerSources = 0;

            try {
                $raplPath = '/sys/class/powercap/intel-rapl/intel-rapl:0/energy_uj';
                if (file_exists($raplPath) && is_readable($raplPath)) {
                    $energy1 = (float)file_get_contents($raplPath);
                    usleep(100000);
                    $energy2 = (float)file_get_contents($raplPath);
                    $powerMicrojoules = $energy2 - $energy1;
                    if ($powerMicrojoules > 0) {
                        $cpuPower = round(($powerMicrojoules * 10) / 1000000, 2);
                        $totalPowerConsumption += $cpuPower;
                        $powerSources++;
                    }
                }
            } catch (\Exception $e) {
            }

            try {
                $hwmonDirs = glob('/sys/class/hwmon/hwmon*/');
                foreach ($hwmonDirs as $dir) {
                    $nameFile = $dir . 'name';
                    if (file_exists($nameFile)) {
                        $name = trim(file_get_contents($nameFile));
                        if ($name === 'amdgpu') {
                            $powerFiles = glob($dir . 'power*_input');
                            foreach ($powerFiles as $powerFile) {
                                if (is_readable($powerFile)) {
                                    $powerValue = trim(file_get_contents($powerFile));
                                    if (is_numeric($powerValue) && $powerValue > 0) {
                                        $gpuPower = round((float)$powerValue / 1000000, 2);
                                        $totalPowerConsumption += $gpuPower;
                                        $powerSources++;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
            }

            if ($powerSources > 0) {
                $powerConsumption = round($totalPowerConsumption, 2);
            }
        }

        return [
            'battery_percentage' => $batteryPercentage,
            'charging' => $charging,
            'power_consumption' => $powerConsumption,
            'time_remaining' => $timeRemaining
        ];
    }


    private function getPowerStats(): array
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            return $this->getMacPowerStats();
        } elseif (PHP_OS_FAMILY === 'Linux') {
            return $this->getLinuxPowerStats();
        }

        return ['battery_percentage' => null, 'charging' => null, 'power_consumption' => null, 'time_remaining' => null];
    }

    private function getMacPowerStats(): array
    {
        $pmsetOutput = shell_exec('pmset -g batt 2>/dev/null');
        $powermetricsOutput = shell_exec('sudo powermetrics -n 1 --hide-cpu-duty-cycle 2>/dev/null | grep -i power');

        $batteryPercentage = null;
        $charging = null;
        $timeRemaining = null;

        if ($pmsetOutput) {
            if (preg_match('/(\d+)%/', $pmsetOutput, $matches)) {
                $batteryPercentage = (int)$matches[1];
            }

            $charging = strpos($pmsetOutput, 'charging') !== false || strpos($pmsetOutput, 'AC Power') !== false;

            if (preg_match('/(\d+:\d+) remaining/', $pmsetOutput, $matches)) {
                $timeRemaining = $matches[1];
            }
        }

        $powerConsumption = null;
        if ($powermetricsOutput) {
            if (preg_match('/(\d+\.?\d*)\s*mW/', $powermetricsOutput, $matches)) {
                $powerConsumption = round((float)$matches[1] / 1000, 2);
            } elseif (preg_match('/(\d+\.?\d*)\s*W/', $powermetricsOutput, $matches)) {
                $powerConsumption = round((float)$matches[1], 2);
            }
        }

        return [
            'battery_percentage' => $batteryPercentage,
            'charging' => $charging,
            'power_consumption' => $powerConsumption,
            'time_remaining' => $timeRemaining
        ];
    }

    private function commandExists(string $command): bool
    {
        $whereIs = shell_exec("which $command 2>/dev/null");
        return !empty($whereIs);
    }

    private function parseGpuTemperature(string $output): ?float
    {
        if (preg_match('/(\d+\.?\d*)\s*[°C]/', $output, $matches)) {
            return (float)$matches[1];
        }

        if (preg_match('/temp[^:]*:\s*(\d+\.?\d*)/', $output, $matches)) {
            return (float)$matches[1];
        }

        return null;
    }

    private function parseGpuUsageMac(string $output): ?float
    {
        if (preg_match('/GPU\s+(\d+\.?\d*)\s*%/', $output, $matches)) {
            return (float)$matches[1];
        }

        if (preg_match('/Render:\s*(\d+\.?\d*)\s*%/', $output, $matches)) {
            return (float)$matches[1];
        }

        return null;
    }

    private function parseGpuMemoryMac(string $output): array
    {
        $total = null;
        $used = null;

        if (preg_match('/(\d+)\s*MB/', $output, $matches)) {
            $total = (float)$matches[1];
        } elseif (preg_match('/(\d+\.?\d*)\s*GB/', $output, $matches)) {
            $total = (float)$matches[1] * 1024;
        }

        return ['total' => $total, 'used' => $used];
    }

    private function parseGpuName(string $output): ?string
    {
        if (preg_match('/Chipset Model:\s*(.+)/', $output, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}

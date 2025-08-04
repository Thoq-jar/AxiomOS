<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller {
    public function index() {
        return view('dashboard');
    }

    public function getSystemStats(): JsonResponse {
        $cpuUsage = $this->getCpuUsage();
        $memoryUsage = $this->getMemoryUsage();
        $temperature = $this->getTemperature();
        $greeting = $this->getGreeting();

        return response()->json([
            'cpu_usage' => $cpuUsage,
            'memory_usage' => $memoryUsage,
            'temperature' => $temperature,
            'greeting' => $greeting,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function getWeather(Request $request): JsonResponse {
        try {
            $location = $this->getLocationFromIP($request);

            if(!$location) {
                return response()->json(['error' => 'Unable to determine location'], 400);
            }

            $weatherData = $this->getOpenMeteoWeather($location['lat'], $location['lon']);

            if(!$weatherData) {
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
        } catch(\Exception $e) {
            return response()->json(['error' => 'Weather service unavailable'], 500);
        }
    }

    private function getLocationFromIP(Request $request): ?array {
        $ip = $request->ip();

        if($ip === '127.0.0.1' || $ip === '::1') {
            $ip = $this->getPublicIP();
        }

        if(!$ip) {
            return null;
        }

        try {
            $response = file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city,lat,lon");
            $data = json_decode($response, true);

            if($data && $data['status'] === 'success') {
                return [
                    'lat' => $data['lat'],
                    'lon' => $data['lon'],
                    'city' => $data['city'],
                    'country' => $data['country']
                ];
            }
        } catch(\Exception $e) {
            return null;
        }

        return null;
    }

    private function getPublicIP(): ?string {
        try {
            $ip = file_get_contents('https://api.ipify.org');
            return trim($ip);
        } catch(\Exception $e) {
            return null;
        }
    }

    private function getOpenMeteoWeather(float $lat, float $lon): ?array {
        try {
            $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m,is_day&timezone=auto";

            $response = file_get_contents($url);
            return json_decode($response, true);
        } catch(\Exception $e) {
            return null;
        }
    }

    private function getWeatherIcon(int $weatherCode, int $isDay): string {
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

    private function getWeatherDescription(int $weatherCode): string {
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

    private function getCpuUsage(): float {
        $load = sys_getloadavg();
        return round($load[0] * 2, 2);
    }

    private function getMemoryUsage(): array {
        if(PHP_OS_FAMILY === 'Darwin') {
            return $this->getMacMemoryUsage();
        } elseif(PHP_OS_FAMILY === 'Linux') {
            return $this->getLinuxMemoryUsage();
        } else {
            return $this->getGenericMemoryUsage();
        }
    }

    private function getMacMemoryUsage(): array {
        $vmStat = shell_exec('vm_stat');
        $memInfo = shell_exec('sysctl -n hw.memsize');

        if(!$vmStat || !$memInfo) {
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

    private function getLinuxMemoryUsage(): array {
        if(!file_exists('/proc/meminfo')) {
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

    private function getGenericMemoryUsage(): array {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);

        if($memoryLimit === '-1') {
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

    private function convertToBytes(string $size): int {
        $unit = strtolower(substr($size, -1));
        $value = (int)substr($size, 0, -1);

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => (int)$size,
        };
    }

    private function getTemperature(): float {
        if(PHP_OS_FAMILY === 'Darwin') {
            $temp = shell_exec('sudo powermetrics --samplers smc -n 1 --hide-cpu-duty-cycle 2>/dev/null | grep "CPU die temperature" | awk \'{print $4}\'');
            if($temp && is_numeric(trim($temp))) {
                return round((float)trim($temp), 1);
            }

            $temp = shell_exec('sudo powermetrics -n 1 --hide-cpu-duty-cycle 2>/dev/null | grep -i temperature | head -1 | awk \'{print $3}\'');
            if($temp && is_numeric(trim($temp))) {
                return round((float)trim($temp), 1);
            }

            return mt_rand(350, 650) / 10;
        } elseif(PHP_OS_FAMILY === 'Linux') {
            $tempFile = '/sys/class/thermal/thermal_zone0/temp';
            if(file_exists($tempFile)) {
                $temp = file_get_contents($tempFile);
                return round($temp / 1000, 1);
            }
        }

        return mt_rand(350, 650) / 10;
    }

    private function getGreeting(): string {
        $hour = shell_exec('date +%H') ?? '00';;
        $user = auth()->user();
        $username = $user ? ucfirst($user->name) : 'Guest';

        if($hour >= 5 && $hour < 12) {
            return "Good Morning, $username";
        } elseif($hour >= 12 && $hour < 18) {
            return "Good Afternoon, $username";
        } elseif($hour >= 18 && $hour < 21) {
            return "Good Evening, $username";
        } else {
            return "Good Night, $username";
        }
    }
}

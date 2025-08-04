<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LogsController extends Controller {
    public function index() {
        return view('logs');
    }

    public function getLogs(Request $request): JsonResponse {
        $level = $request->get('level', '');
        $limit = $request->get('limit', 100);
        $file = $request->get('file', '');

        try {
            $files = $this->getLogFiles();

            if($file && File::exists($file)) {
                $logs = $this->parseLogFile($file, $level, $limit);
            } else {
                $logs = $this->getAllSystemLogs($level, $limit);
            }

            $statistics = $this->calculateStatistics($logs);

            return response()->json([
                'logs' => $logs,
                'statistics' => $statistics,
                'files' => $files
            ]);

        } catch(\Exception $e) {
            Log::error('Failed to read logs: ' . $e->getMessage());

            return response()->json([
                'logs' => [],
                'statistics' => ['total' => 0, 'error' => 0, 'warning' => 0, 'info' => 0, 'debug' => 0],
                'files' => [],
                'error' => 'Failed to read logs: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clearLogs(): JsonResponse {
        try {
            $logPath = storage_path('logs/laravel.log');

            if(File::exists($logPath)) {
                File::put($logPath, '');
                Log::info('Logs cleared by user');
            }

            return response()->json(['success' => true]);

        } catch(\Exception $e) {
            Log::error('Failed to clear logs: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to clear logs'], 500);
        }
    }

    private function getAllSystemLogs(string $filterLevel = '', int $limit = 100): array {
        $allLogs = [];
        $files = $this->getLogFiles();

        foreach($files as $file) {
            try {
                $fileLogs = $this->parseLogFile($file['path'], $filterLevel, $limit);
                $allLogs = array_merge($allLogs, $fileLogs);
            } catch(\Exception $e) {
                continue;
            }
        }

        usort($allLogs, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($allLogs, 0, $limit);
    }

    private function parseLogFile(string $logPath, string $filterLevel = '', int $limit = 100): array {
        if(!File::exists($logPath) || !File::isReadable($logPath)) {
            return [];
        }

        $content = File::get($logPath);
        $lines = explode("\n", $content);
        $logs = [];
        $currentLog = null;

        $filename = basename($logPath);

        foreach(array_reverse($lines) as $line) {
            if(empty(trim($line))) {
                continue;
            }

            $logEntry = $this->parseLogLine($line, $filename);

            if($logEntry) {
                if($currentLog && (empty($filterLevel) || strtolower($currentLog['level']) === strtolower($filterLevel))) {
                    $logs[] = $currentLog;
                    if(count($logs) >= $limit) {
                        break;
                    }
                }
                $currentLog = $logEntry;
            } else {
                if($currentLog) {
                    $currentLog['context'] = trim($line . "\n" . $currentLog['context']);
                }
            }
        }

        if($currentLog && (empty($filterLevel) || strtolower($currentLog['level']) === strtolower($filterLevel))) {
            $logs[] = $currentLog;
        }

        return array_reverse($logs);
    }

    private function parseLogLine(string $line, string $filename): ?array {
        if(preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?\.(\w+): (.+)$/', $line, $matches)) {
            return [
                'timestamp' => $matches[1],
                'level' => strtolower($matches[2]),
                'message' => $matches[3],
                'context' => '',
                'source' => $filename
            ];
        }

        if(preg_match('/^(\w{3}\s+\d{1,2}\s+\d{2}:\d{2}:\d{2})\s+\S+\s+(.+?):\s*(.+)$/', $line, $matches)) {
            return [
                'timestamp' => date('Y-m-d H:i:s', strtotime($matches[1])),
                'level' => $this->guessLogLevel($matches[3]),
                'message' => $matches[2] . ': ' . $matches[3],
                'context' => '',
                'source' => $filename
            ];
        }

        if(preg_match('/^(\S+)\s+\S+\s+\S+\s+\[([^\]]+)\]\s+"([^"]+)"\s+(\d+)\s+(\d+)/', $line, $matches)) {
            return [
                'timestamp' => date('Y-m-d H:i:s', strtotime($matches[2])),
                'level' => $matches[4] >= 400 ? 'error' : 'info',
                'message' => $matches[3] . ' - Status: ' . $matches[4],
                'context' => '',
                'source' => $filename
            ];
        }

        if(preg_match('/^(\d{4}-\d{2}-\d{2}[\sT]\d{2}:\d{2}:\d{2}).*?(.+)$/', $line, $matches)) {
            return [
                'timestamp' => $matches[1],
                'level' => $this->guessLogLevel($matches[2]),
                'message' => $matches[2],
                'context' => '',
                'source' => $filename
            ];
        }

        return null;
    }

    private function guessLogLevel(string $message): string {
        $message = strtolower($message);

        if(str_contains($message, 'error') || strpos($message, 'fatal') !== false) {
            return 'error';
        }
        if(str_contains($message, 'warn')) {
            return 'warning';
        }
        if(str_contains($message, 'debug')) {
            return 'debug';
        }

        return 'info';
    }

    private function calculateStatistics(array $logs): array {
        $stats = ['total' => 0, 'error' => 0, 'warning' => 0, 'info' => 0, 'debug' => 0];

        foreach($logs as $log) {
            $stats['total']++;
            $level = strtolower($log['level']);
            if(isset($stats[$level])) {
                $stats[$level]++;
            }
        }

        return $stats;
    }

    private function getLogFiles(): array {
        $files = [];

        $logDirectories = [
            '/var/log',
            '/var/log/apache2',
            '/var/log/nginx',
            '/var/log/mysql',
            '/var/log/php',
            '/usr/local/var/log',
            storage_path('logs'),
        ];

        foreach($logDirectories as $directory) {
            if(File::isDirectory($directory) && File::isReadable($directory)) {
                $this->scanDirectoryForLogs($directory, $files);
            }
        }

        usort($files, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        return $files;
    }

    private function scanDirectoryForLogs(string $directory, array &$files): void {
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach($iterator as $file) {
                if($file->isFile() && $this->isLogFile($file->getPathname())) {
                    try {
                        $files[] = [
                            'name' => $file->getFilename(),
                            'path' => $file->getPathname(),
                            'size' => $file->getSize(),
                            'modified' => $file->getMTime(),
                            'directory' => dirname($file->getPathname())
                        ];
                    } catch(\Exception $e) {
                        continue;
                    }
                }
            }
        } catch(\Exception $e) {
            return;
        }
    }

    private function isLogFile(string $filepath): bool {
        $filename = basename($filepath);
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        if(in_array($extension, ['log', 'txt'])) {
            return true;
        }

        $logPatterns = [
            '/\.log$/',
            '/\.log\.\d+$/',
            '/access_log/',
            '/error_log/',
            '/messages/',
            '/syslog/',
            '/auth\.log/',
            '/kern\.log/',
            '/mail\.log/',
            '/cron\.log/',
        ];

        foreach($logPatterns as $pattern) {
            if(preg_match($pattern, $filename)) {
                return true;
            }
        }

        return false;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class AppStoreController extends Controller {
    private function getAppsData(): array {
        $jsonPath = resource_path('data/apps.json');
        if(!File::exists($jsonPath)) {
            return [];
        }
        return json_decode(File::get($jsonPath), true) ?? [];
    }

    public function index() {
        return view('appstore');
    }

    public function getApps(): JsonResponse {
        $apps = $this->getAppsData();
        return response()->json($apps);
    }

    public function getFeaturedApps(): JsonResponse {
        $apps = $this->getAppsData();
        $featured = array_filter($apps, fn($app) => $app['featured'] ?? false);
        return response()->json($featured);
    }

    public function getCategories(): JsonResponse {
        $apps = $this->getAppsData();
        $categories = array_unique(array_column($apps, 'category'));
        return response()->json($categories);
    }

    public function installApp(Request $request, string $appId): JsonResponse {
        $apps = $this->getAppsData();

        if(!isset($apps[$appId])) {
            return response()->json(['error' => 'App not found'], 404);
        }

        $app = $apps[$appId];
        $commands = $app['commands'] ?? [];
        $results = [];

        foreach($commands as $index => $command) {
            try {
                $result = Process::run($command);
                $results[] = [
                    'command' => $command,
                    'success' => $result->successful(),
                    'output' => $result->output(),
                    'error' => $result->errorOutput()
                ];

                if(!$result->successful()) {
                    return response()->json([
                        'error' => 'Installation failed at step ' . ($index + 1),
                        'results' => $results
                    ], 500);
                }
            } catch(\Exception $e) {
                return response()->json([
                    'error' => 'Command execution failed: ' . $e->getMessage(),
                    'results' => $results
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $app['name'] . ' installed successfully',
            'results' => $results
        ]);
    }
}

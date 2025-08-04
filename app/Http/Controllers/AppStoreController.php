<?php

namespace App\Http\Controllers;

use App\Models\App;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppStoreController extends Controller {
    public function index() {
        return view('appstore');
    }

    public function getApps(): JsonResponse {
        $apps = App::all();
        return response()->json($apps);
    }

    public function getFeaturedApps(): JsonResponse {
        $apps = App::where('featured', true)->get();
        return response()->json($apps);
    }

    public function getAppsByCategory(string $category): JsonResponse {
        $apps = App::where('category', $category)->get();
        return response()->json($apps);
    }

    public function getApp(int $id): JsonResponse {
        $app = App::findOrFail($id);
        return response()->json($app);
    }

    public function installApp(Request $request, int $id): JsonResponse {
        $app = App::findOrFail($id);

        return response()->json([
            'message' => 'App installation started',
            'app_id' => $id,
            'status' => 'installing'
        ]);
    }

    public function uninstallApp(Request $request, int $id): JsonResponse {
        $app = App::findOrFail($id);

        return response()->json([
            'message' => 'App uninstallation started',
            'app_id' => $id,
            'status' => 'uninstalling'
        ]);
    }

    public function getInstallationStatus(int $id): JsonResponse {
        return response()->json([
            'app_id' => $id,
            'status' => 'completed',
            'progress' => 100
        ]);
    }

    public function searchApps(Request $request): JsonResponse {
        $query = $request->get('q', '');
        $apps = App::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->get();

        return response()->json($apps);
    }
}

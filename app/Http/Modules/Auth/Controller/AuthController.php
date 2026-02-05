<?php

namespace App\Http\Modules\Auth\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Modules\Auth\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    public function login(Request $request)
    {
        try {
            $auth = $this->authService->login($request->all());
            return response()->json($auth, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $auth = $this->authService->register($request->all());
            return response()->json($auth, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}

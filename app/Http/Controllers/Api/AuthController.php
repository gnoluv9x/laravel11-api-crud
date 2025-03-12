<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // register
    public function register(StoreUserRequest $request)
    {
        try {
            $validated = $request->validated();
            unset($validated['password_confirm']);

            $validated['level'] = 1;
            $validated['password'] = Hash::make($validated['password']);
            $validated['created_at'] = Carbon::now()->timestamp;

            $user = User::create($validated);

            return response()->json(['message' => 'Register successfully', 'status' => 'Success']);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $refreshToken = $this->generateRefreshToken();

        return $this->respondWithToken($token, $refreshToken);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(): JsonResponse
    {
        try {
            $currentToken = request()->get('refresh_token');

            if (!$currentToken) {
                return response()->json(['message' => 'Token can not empty'], 401);
            }

            $payload = JWTAuth::getJWTProvider()->setSecret(config("jwt.refresh_secret"))->decode($currentToken);

            if (!$payload || !$payload['user']) {
                return response()->json(['message' => 'Token invalid'], 401);
            }

            $user = User::find($payload['user'])->first();

            if (!$user) {
                return response()->json(['message' => 'User\'s not exists'], 401);
            }

            $token = auth('api')->login($user);
            $refreshToken = $this->generateRefreshToken();

            return $this->respondWithToken($token, $refreshToken);
        } catch (\Exception $th) {
            //throw $th;
            return response()->json(['message' => $th->getMessage()]);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $refreshToken)
    {
        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    private function generateRefreshToken()
    {
        $data = [
            'user' => auth('api')->user()->id,
            'exp' => time() + config('jwt.refresh_ttl') * 60
        ];

        $refreshToken = JWTAuth::getJWTProvider()->setSecret(config("jwt.refresh_secret"))->encode($data);

        return $refreshToken;
    }
}

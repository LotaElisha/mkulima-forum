<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\AuthenticateEmail;
use App\Actions\Auth\RegisterFarmer;
use App\Actions\Auth\ResetPassword;
use App\Actions\Auth\SendPasswordReset;
use App\Http\Api\V1Response;
use App\Http\Api\V1User;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const INVALID_LOGIN = 'Barua pepe au nenosiri si sahihi.';

    public function register(Request $request, RegisterFarmer $register): JsonResponse
    {
        if ($request->filled('email')) {
            $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:users,pending_email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'confirmed', ...PasswordController::passwordRules()],
            'role' => ['sometimes', 'nullable', 'in:farmer'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'preferred_language' => ['nullable', 'in:sw,en'],
        ]);

        $user = DB::transaction(fn () => $register($validated));
        $user->sendEmailVerificationNotification();

        return V1Response::ok([
            'token' => $user->createToken('mobile-app', ['*'])->plainTextToken,
            'token_type' => 'Bearer',
            'user' => V1User::from($user),
        ], status: 201);
    }

    public function login(Request $request, AuthenticateEmail $authenticate): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = $authenticate($request->input('email'), $request->input('password'));

        if (! $user) {
            return V1Response::error(self::INVALID_LOGIN, [
                'email' => [self::INVALID_LOGIN],
            ], 401);
        }

        return V1Response::ok([
            'token' => $user->createToken('mobile-app', ['*'])->plainTextToken,
            'token_type' => 'Bearer',
            'user' => V1User::from($user),
        ]);
    }

    public function forgot(Request $request, SendPasswordReset $send): JsonResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);

        $send($request->input('email'));

        return V1Response::ok([
            'message' => __('auth_flows.reset_link_sent'),
        ]);
    }

    public function reset(Request $request, ResetPassword $reset): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', ...PasswordController::passwordRules()],
        ]);

        $status = $reset(
            $request->input('email'),
            $request->input('password'),
            $request->input('password_confirmation'),
            $request->input('token'),
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__('auth_flows.reset_failed')],
            ]);
        }

        return V1Response::ok([
            'message' => __('auth_flows.reset_done'),
        ]);
    }

    public function verifyEmail(Request $request, string $uuid, string $hash): JsonResponse
    {
        $user = User::query()->where('uuid', $uuid)->first();

        if (! $user || ! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return V1Response::error('Kiungo si sahihi au kimeisha muda.', status: 403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return V1Response::ok([
            'email_verified' => true,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return V1Response::ok([
            'user' => V1User::from($request->user()),
        ]);
    }

    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->pending_email) {
            $user->sendPendingEmailVerificationNotification();
        } elseif ($user->email && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return V1Response::ok([
            'message' => __('auth_flows.verification_sent'),
        ]);
    }

    public function emailStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        return V1Response::ok([
            'email' => $user->email,
            'email_verified' => $user->hasVerifiedEmail(),
            'pending_email' => $user->pending_email,
        ]);
    }
}

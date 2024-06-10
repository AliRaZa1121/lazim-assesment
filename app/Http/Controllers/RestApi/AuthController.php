<?php

namespace App\Http\Controllers\RestApi;

use App\Http\Controllers\Controller;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Traits\EmailTrait;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{

    use EmailTrait;

    // Login function
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {

                $user = User::where('email', $request->email)->first();

                if ($user->email_verified_at == null) {
                    $otp = Token::generateToken($user->id, 'email_verification');

                    $this->sendMail([
                        'app_name' => env('APP_NAME'),
                        'email' => $request->email,
                        'name' => $user->name,
                        'subject' => 'Email Verification',
                        'body' => "You have requested to login to your account. To complete your login, please verify your email address by using the following One Time Password (OTP):<br><br>"
                            . "<strong>" . $otp . "</strong><br><br>"
                            . "This OTP will expire in 60 minutes.<br><br>"
                            . "If you did not request this login, please ignore this email.<br><br>"
                    ], 'email-templates.common');

                    return $this->sendError('Unauthorized.', 'Email not verified, We have sent you an email please verify your email first', 401);
                }

                $token = $user->createToken('app-token')->plainTextToken;

                return $this->sendResponse(['token' => $token, 'user' => $user], 'User logged in successfully.');
            }

            return $this->sendError('Unauthorized.', ['Unauthorized.'], 401);
        } catch (\Throwable $th) {
            return $this->sendError('Server Error.', $th->getMessage(), 500);
        }
    }

    // Register function
    public function register(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required|max:50|min:3',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $input = $request->all();
            $input['password'] = Hash::make($input['password']);
            $user = User::create($input);

            $otp = Token::generateToken($user->id, 'email_verification');
            $this->sendMail([
                'app_name' => env('APP_NAME'),
                'email' => $request->email,
                'name' => $user->name,
                'subject' => 'Email Verification',
                'body' => "Thank you for signing up! To complete your registration, please verify your email address by using the following One Time Password (OTP):<br><br>"
                    . "<strong>" . $otp . "</strong><br><br>"
                    . "This OTP will expire in 60 minutes.<br><br>"
                    . "If you did not sign up for this account, please ignore this email.<br><br>"
            ], 'email-templates.common');

            return $this->sendResponse(['user' => $user], 'User registered successfully, Please verify your email.');
        } catch (\Throwable $th) {
            return $this->sendError('Server Error.', $th->getMessage(), 500);
        }
    }

    // Resend verify email function
    public function resendVerifyEmail(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $user = User::where('email', $request->email, 'email_verified_at', null)->first();

            if (empty($user)) {
                return $this->sendError('Email already verified.', ['Email already verified.'], 400);
            }

            $otp = Token::generateToken($user->id, 'email_verification');
            $this->sendMail([
                'app_name' => env('APP_NAME'),
                'email' => $request->email,
                'name' => $user->name,
                'subject' => 'Email Verification',
                'body' => "You have requested to verify your email address. To complete your email verification, please use the following One Time Password (OTP):<br><br>"
                    . "<strong>" . $otp . "</strong><br><br>"
                    . "This OTP will expire in 60 minutes.<br><br>"
                    . "If you did not request this verification, please ignore this email.<br><br>"
            ], 'email-templates.common');

            return $this->sendResponse(['message' => 'OTP sent to your email.'], 'OTP sent to your email.');
        } catch (\Throwable $th) {
            return $this->sendError('Server Error.', $th->getMessage(), 500);
        }
    }

    public function verifyEmail(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'otp' => 'required|numeric|digits:4',
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $user = User::where('email', $request->email)->first();

            $token = Token::verifyToken($user->id, $request->otp, 'email_verification');
            if (!$token) {
                return $this->sendError('Invalid OTP.', 'Invalid OTP.', 400);
            }

            if ($token->isExpired()) {
                return $this->sendError('OTP expired.', 'OTP expired.', 400);
            }

            $user->email_verified_at = now();
            $user->save();

            $token->revoke();
            return $this->sendResponse(['user' => $user], 'Email verified successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Server Error.', $th->getMessage(), 500);
        }
    }


    // Forgot password function
    public function forgotPassword(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $user = User::where('email', $request->email)->first();

            $otp = Token::generateToken($user->id, 'reset_password');
            $this->sendMail([
                'app_name' => env('APP_NAME'),
                'email' => $request->email,
                'name' => $user->name,
                'subject' => 'Reset Password',
                'body' => "You have requested to reset your password. To complete your password reset, please use the following One Time Password (OTP):<br><br>"
                    . "<strong>" . $otp . "</strong><br><br>"
                    . "This OTP will expire in 60 minutes.<br><br>"
                    . "If you did not request this password reset, please ignore this email.<br><br>"
            ], 'email-templates.common');

            return $this->sendResponse(['message' => 'OTP sent to your email.'], 'OTP sent to your email.');
        } catch (\Throwable $th) {
            return $this->sendError('Server Error.', $th->getMessage(), 500);
        }
    }

    // Reset password function
    public function resetPassword(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'otp' => 'required|numeric|digits:4',
                'email' => 'required|email|exists:users,email',
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->sendError('User not found.', 'User not found.', 404);
            }

            $token = Token::verifyToken($user->id, $request->otp, 'reset_password');
            if (!$token) {
                return $this->sendError('Invalid OTP.', 'Invalid OTP.', 400);
            }

            if ($token->isExpired()) {
                return $this->sendError('OTP expired.', 'OTP expired.', 400);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            $token->revoke();
            return $this->sendResponse(['user' => $user], 'Password reset successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Server Error.', $th->getMessage(), 500);
        }
    }


    // Logout function
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return $this->sendResponse([], 'User logged out successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Server Error.', $th->getMessage(), 500);
        }
    }
}

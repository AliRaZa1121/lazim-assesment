<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function revoke()
    {
        $this->delete();
    }

    public function isExpired()
    {
        return $this->updated_at->diffInMinutes(now()) >= 60;
    }

    public static function generateToken($user_id, $type)
    {
        $otp = rand(1000, 9999);

        self::where('user_id', $user_id)
            ->where('type', $type)
            ->delete();

        self::create([
            'user_id' => $user_id,
            'token' => $otp,
            'type' => $type
        ]);

        return $otp;
    }

    public static function verifyToken($user_id, $otp, $type)
    {
        return self::where('user_id', $user_id)
            ->where('token', $otp)
            ->where('type', $type)
            ->first();
    }

    public static function revokeToken($user_id, $type)
    {
        return self::where('user_id', $user_id)
            ->where('type', $type)
            ->delete();
    }
}

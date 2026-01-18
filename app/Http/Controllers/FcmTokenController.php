<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        FcmToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => auth()->id(),
                'device' => $request->header('User-Agent')
            ]
        );

        return response()->json(['message' => 'Token saved']);
    }
}


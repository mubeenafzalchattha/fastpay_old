<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushNotificationController extends Controller
{
    public function saveDeviceToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        $deviceToken = DeviceToken::where('token', $request->token)->first();

        if (!$deviceToken) {
            $deviceToken        = new DeviceToken();
            $deviceToken->token = $request->token;
        }

        $deviceToken->user_id  = Auth::id();
        $deviceToken->save();

        $notify[] = 'Token saved successfully';

        return response()->json([
            'remark'    => 'token_saved',
            'status' => 'success',
            'message' => ['success'=>$notify],
            'data' => [
                'token' => $deviceToken->token
            ]
        ]);
    }
}

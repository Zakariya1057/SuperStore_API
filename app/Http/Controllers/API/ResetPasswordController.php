<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\MailTrait;
use App\Traits\SanitizeTrait;
use App\Traits\UserTrait;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller {
    
    use MailTrait;
    use UserTrait;
    use SanitizeTrait;

    // Reset Password -> Send code
    // Validate -> Validate code
    // New Password -> Update Password & Send code

    public function send_code(Request $request){

        $validated_data = $request->validate([
            'data.email' => 'required|email|max:255',
        ]);

        $validated_data = $this->sanitizeAllFields($validated_data);

        $data = $validated_data['data'];

        $user = User::where('email',$data['email'])->get()->first();

        if(is_null($user)){
            Log::error('No user found with email: '. $data['email']);
        } else {
            $code = mt_rand(1000000,9999999);
            User::where('id', $user->id)->update(['remember_token' => $code, 'token_sent_at' => Carbon::now()]);
            $this->mail_reset_password($user->email,$code,$user->name);
        }

        return response()->json(['data' => ['status' => 'success']]);

    }

    public function validate_code(Request $request){

        $validated_data = $request->validate([
            'data.email' => 'required|email|max:255',
            'data.code' => 'required|integer'
        ]);

        $data = $this->sanitizeAllFields($validated_data['data']);

        $user = User::where([['email',$data['email']], ['remember_token', $data['code']] ])->get()->first();
        if(is_null($user)){
            throw new Exception('Invalid code.', 422);
        }

        $token_time_diff = Carbon::createFromFormat('Y-m-d H:i:s', $user->token_sent_at)->diffInHours(NOW());
        if($token_time_diff >= 4){
            throw new Exception('Code expired please try sending another email.', 422);
        }

        return response()->json(['data' => ['status' => 'success']]);
    }

    public function new_password(Request $request){

        $validated_data = $request->validate([
            'data.code' => 'required|integer',
            'data.email' => 'required|email|max:255',
            'data.password' => 'required|confirmed|string|min:8|max:255',
        ]);

        $data = $this->sanitizeAllFields($validated_data['data']);

        $user = User::where('email', $data['email'])->get()->first();

        if(!$user){
            // This really shouldnt happen
            return response()->json(['data' => ['status' => 'success']]);
        }

        $token_time_diff = Carbon::createFromFormat('Y-m-d H:i:s', $user->token_sent_at)->diffInHours(NOW());

        if($token_time_diff){
            throw new Exception('Code expired please try again.', 422);
        }
        
        User::where([ ['email', $data['email']],['remember_token', $data['code']] ])->update([
            'remember_token' => null,
            'password' => Hash::make($data['password']),
        ]);

        
        $user->tokens()->delete();
        $token = $user->createToken($user->id)->plainTextToken;

        User::where('id', $user->id)->update(['logged_in_at' => Carbon::now()]);
        return response()->json(['data' => ['id' => $user->id, 'token' => $token, 'name' => $user->name, 'email' => $user->email]]);

    }

}

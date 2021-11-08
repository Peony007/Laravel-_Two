<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Gmail;
use App\Mail\PasswordResetEmail;

class UserAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'type' => 'integer',
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        $request['password'] = Hash::make($request['password']);
        $request['type'] = $request['type'] ? $request['type'] : 0;
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());

        $encrypt_method = "AES-256-CBC";
        $secret_key = '7aE3OKIZxusugQdpk3gwNi9x63MRAFLgkMJ4nyil88ZYMyjqTSE3FIo8L5KJghfi';
        $secret_iv = '7aE3OKIZxusugQdpk3gwNi9x63MRAFLgkMJ4nyil88ZYMyjqTSE3FIo8L5KJghfi';
        $key = hash('sha256', $secret_key);

        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        $string = json_encode(array(["name" => $request["name"], "email" => $request["email"], "password" => $request['password']]));
        $encryptToken = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $encryptToken = base64_encode($encryptToken);

        Mail::to($request['email'])->send(new Gmail($request['email'], $encryptToken));
        $response = ["success" => 'true', 'message' => 'Registeration Success', 'user' => $user];
        return response($response, 200);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $verified = $user->email_verified_at;
            if($verified === null) {
                $response = ["message" => "not verified"];
                return response($response, 422);
            }
            if ($verified !== null) {
                if (Hash::check($request->password, $user->password)) {
                    $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                    $response = ['success' => 'true', 'message' => 'Login Success', 'token' => $token, 'user' => $user];
                    return response($response, 200);
                } else {
                    $response = ["message" => "Password mismatch"];
                    return response($response, 422);
                }
            }
        } else {
            $response = ["message" => 'User does not exist'];
            return response($response, 422);
        }
    }
    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        $response = ['message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }
    public function user(Request $request)
    {
        $response =  $request->user();
        return response($response, 200);
    }
    public function verify(Request $request)
    {
        $data = ($request->query());
        $encrypt_method = "AES-256-CBC";
        $secret_key = '7aE3OKIZxusugQdpk3gwNi9x63MRAFLgkMJ4nyil88ZYMyjqTSE3FIo8L5KJghfi';
        $secret_iv = '7aE3OKIZxusugQdpk3gwNi9x63MRAFLgkMJ4nyil88ZYMyjqTSE3FIo8L5KJghfi';
        $key = hash('sha256', $secret_key);

        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        $decryptToken = openssl_decrypt(base64_decode($data["token"]), $encrypt_method, $key, 0, $iv);

        $decryptTokenArray = json_decode($decryptToken);
        $email = ($decryptTokenArray[0]->email);
        $now = date("Y-m-d H:i:s");
        $user = User::where('email', $email)->first()->update(['email_verified_at' => $now]);
        return view('verifiysuccess');
    }
    public function sendresetpasswordemail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        $request['password'] = Hash::make($request['password']);

        $encrypt_method = "AES-256-CBC";
        $secret_key = '7aE3OKIZxusugQdpk3gwNi9x63MRAFLgkMJ4nyil88ZYMyjqTSE3FIo8L5KJghfi';
        $secret_iv = '7aE3OKIZxusugQdpk3gwNi9x63MRAFLgkMJ4nyil88ZYMyjqTSE3FIo8L5KJghfi';
        $key = hash('sha256', $secret_key);

        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        $string = json_encode(array(["email" => $request["email"], "password" => $request['password']]));
        $encryptToken = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $encryptToken = base64_encode($encryptToken);

        Mail::to($request['email'])->send(new PasswordResetEmail($request['email'], $encryptToken));
        $response = ["success" => 'true', 'message' => 'Send Resetpassword Email Success'];
        return response($response, 200);
    }

    public function resetpassword(Request $request)
    {
        $data = ($request->query());
        $encrypt_method = "AES-256-CBC";
        $secret_key = '7aE3OKIZxusugQdpk3gwNi9x63MRAFLgkMJ4nyil88ZYMyjqTSE3FIo8L5KJghfi';
        $secret_iv = '7aE3OKIZxusugQdpk3gwNi9x63MRAFLgkMJ4nyil88ZYMyjqTSE3FIo8L5KJghfi';
        $key = hash('sha256', $secret_key);

        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        $decryptToken = openssl_decrypt(base64_decode($data["token"]), $encrypt_method, $key, 0, $iv);

        $decryptTokenArray = json_decode($decryptToken);
        $email = ($decryptTokenArray[0]->email);
        $password = ($decryptTokenArray[0]->password);
        $user = User::where('email', $email)->first()->update(['password' => $password]);
        return view('resetpasswordsuccess');
    }
}

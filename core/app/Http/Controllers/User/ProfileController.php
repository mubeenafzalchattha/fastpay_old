<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function profile()
    {
        $pageTitle = "Profile Setting";
        $user = auth()->user();
        return view($this->activeTemplate . 'user.profile_setting', compact('pageTitle', 'user'));
    }

    public function submitProfile(Request $request)
    {
        $request->validate([
            'firstname'     => 'required|string',
            'lastname'      => 'required|string',
            'identity_no'      => 'required|string',
            'image'         => ['image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'id_front'      => ['image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'id_back'       => ['image', new FileTypeValidate(['jpg', 'jpeg', 'png'])]
        ], [
            'firstname.required' => 'First name field is required',
            'lastname.required'  => 'Last name field is required'
        ]);

        $user = auth()->user();

        $user->firstname = $request->firstname;
        $user->lastname  = $request->lastname;
        $user->identity_no  = $request->identity_no;

        $user->address = [
            'address' => $request->address,
            'state'   => $request->state,
            'zip'     => $request->zip,
            'country' => @$user->address->country,
            'city'    => $request->city,
        ];

        if ($request->hasFile('id_back')) {
            $id_back = fileUploader($request->id_back, getFilePath('userProfile'), getFileSize('userProfile'), @$user->id_back);
            $user->id_back = $id_back;
        }

        if ($request->hasFile('id_front')) {
            $id_front = fileUploader($request->id_front, getFilePath('userProfile'), getFileSize('userProfile'), @$user->id_front);
            $user->id_front = $id_front;
        }
        $user->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return back()->withNotify($notify);
    }

    public function changePassword()
    {
        $pageTitle = 'Change Password';
        return view($this->activeTemplate . 'user.password', compact('pageTitle'));
    }

    public function submitPassword(Request $request)
    {

        $passwordValidation = Password::min(6);
        $general = gs();
        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $this->validate($request, [
            'current_password' => 'required',
            'password' => ['required', 'confirmed', $passwordValidation]
        ]);

        $user = auth()->user();
        if (Hash::check($request->current_password, $user->password)) {
            $password       = Hash::make($request->password);
            $user->password = $password;
            $user->save();
            $notify[] = ['success', 'Password changes successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'The password doesn\'t match!'];
            return back()->withNotify($notify);
        }
    }
}

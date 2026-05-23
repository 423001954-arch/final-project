<?php

namespace App\Controllers;

use App\Models\UserModel;

class StaffController extends BaseController
{
    public function dashboard()
    {
        $sessionUser = session('user');

        if (! $sessionUser || empty($sessionUser['email'])) {
            return redirect()->to('/login');
        }

        $user = (new UserModel())->where('email', $sessionUser['email'])->first();

        if (! $user) {
            session()->destroy();

            return redirect()->to('/login');
        }

        return view('staff/dashboard', ['user' => $user]);
    }
}

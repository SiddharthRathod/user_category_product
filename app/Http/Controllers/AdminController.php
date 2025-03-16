<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        // Fetch all users
        $users = User::all();

        // Return view with users
        return view('admin.users.index', compact('users'));
    }
}


<?php

namespace App\Http\Controllers\Admin; // <-- Notez le namespace !

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // On renvoie vers une page Vue dans un dossier Admin
        return inertia('Admin/Dashboard'); 
    }
}
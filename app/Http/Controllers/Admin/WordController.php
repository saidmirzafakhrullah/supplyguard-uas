<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WordController extends Controller
{
    public function index()
    {
        return view('admin.words.index');
    }
}
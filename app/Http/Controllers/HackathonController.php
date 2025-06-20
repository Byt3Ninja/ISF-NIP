<?php

namespace App\Http\Controllers;
// use App\Hackathon;
use Illuminate\Http\Request;

class HackathonController extends Controller
{
    public function index()
    {
        // $hackathons = Hackathon::all();
        // return view('admin.hackathon.index', compact('hackathons'));
        return view('admin.hackathon.index');
    }
}
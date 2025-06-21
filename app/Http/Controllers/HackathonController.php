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

    public function manage( )
    {
        return view('admin.hackathon.manage-team');
    }

    public function myTeam()
    {
        // $hackathon = Hackathon::findOrFail($id);
        // return view('admin.hackathon.my-team', compact('hackathon'));
        return view('admin.hackathon.team-workspace');
    }

    public function show() {
        // $hackathon = Hackathon::findOrFail($id);
        // return view('admin.hackathon.show', compact('hackathon'));
        return view('admin.hackathon.hackathon-details');
    }
}
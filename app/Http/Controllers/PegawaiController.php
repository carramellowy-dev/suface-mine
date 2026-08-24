<?php

namespace App\Http\Controllers;

use App\Models\Ritasi;
use App\Models\NonRitasi;
use App\Models\Area;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PegawaiController extends Controller
{
    public function dashboard()
    {
        return redirect()->route('pegawai.ritasi.create');
    }

    public function index()
    {
        return redirect()->route('pegawai.ritasi.create');
    }
}

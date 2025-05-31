<?php

namespace App\Http\Controllers;

// ============================================================================>> Core Library
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; //performing authorization checks and handling unauthorized access
use Illuminate\Foundation\Bus\DispatchesJobs; // Handling queue process
use Illuminate\Foundation\Validation\ValidatesRequests; // Input Validation
use Illuminate\Routing\Controller;

class MainController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}

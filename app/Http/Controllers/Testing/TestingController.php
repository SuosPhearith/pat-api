<?php

namespace App\Http\Controllers\Testing;

// ============================================================================>> Core Library
use Illuminate\Http\Request; // For Getting requested Payload from Client
use Illuminate\Http\Response; // For Responsing data back to Client

use App\Models\Product\Product;

class TestingController
{
    public function calculate(Request $req){


        $data = Product::select('id', 'name')
        ->where('name', 'LIKE', '%'.$req->q.'%')
        ->get();

        return $data;

    }

    private function _sum($x = 0, $y = 0){

        return $x+$y;

    }

}



<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    public function generate(Request $request)
    {
        $data = $request->query('data');
        $size = (int) ($request->query('size', 320));

        if (!$data) {
            abort(400, 'Missing data parameter');
        }

        $size = min(max($size, 100), 1000);

        $svg = QrCode::size($size)
            ->margin(2)
            ->errorCorrection('M')
            ->generate($data);

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}

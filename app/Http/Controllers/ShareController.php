<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShareController extends Controller
{
    public function uploadScreenshot(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        try {
            // Extract base64 data
            $imageData = $request->input('image');
            
            // Remove data:image/png;base64, prefix if present
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif
            } else {
                $type = 'png';
            }

            // Decode base64
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return response()->json(['error' => 'Invalid image data'], 400);
            }

            // Generate unique filename
            $filename = 'calculation_' . Str::random(32) . '.' . $type;
            $path = 'calculations/' . $filename;

            // Store in public disk
            Storage::disk('public')->put($path, $imageData);

            // Generate public URL
            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'url' => $url,
                'path' => $path,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSharedCalculation($filename)
    {
        $path = 'calculations/' . $filename;
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $filePath = Storage::disk('public')->path($path);
        
        return response()->file($filePath);
    }
}

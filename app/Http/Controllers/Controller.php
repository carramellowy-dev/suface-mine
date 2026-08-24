<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    public function wantsJson(Request $request): bool
    {
        return $request->acceptsJson()
            || $request->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function success(Request $request, mixed $payload = null)
    {
        if ($this->wantsJson($request)) {
            $data = ['success' => true];
            if ($payload !== null) {
                $data = array_merge($data, ['payload' => $payload]);
            }
            return response()->json($data);
        }

        if (session()->has('success')) {
            return back()->with('success', session('success'));
        }
        
        return back()->with('success', 'Operation completed successfully.');
    }

    public function failure(Request $request, string $message)
    {
        if ($this->wantsJson($request)) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        return back()->with('error', $message)->withInput();
    }
}
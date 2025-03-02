<?php

namespace App\Trait;

trait CustomRespone
{
    function json($code, $status, $message, $data = [], $errors = [])
    {
        return response()->json([
            'code' => $code,
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ], $code);
    }
}

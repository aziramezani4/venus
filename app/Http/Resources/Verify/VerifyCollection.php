<?php

namespace App\Http\Resources\Verify;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VerifyCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }

    public function with($request)
    {
        return [
            'status' => 'OK',
            'message' => 'Success',
            'isSuccess' => true,
            'errors' => [],
        ];
    }
}

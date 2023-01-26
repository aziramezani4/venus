<?php

namespace App\Http\Resources\Customer;

use App\Models\Account;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_name' => $this->user_name,
            'phone_number' => $this->phone_number,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'last_signin' => $this->last_signin,
            'profile_status' => $this->profile_status ?'true':'false',
        ];
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

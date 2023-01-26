<?php

namespace App\Http\Resources\Verify;

use App\Http\Resources\Customer\CustomerResource;
use App\Models\Account;
use Illuminate\Http\Resources\Json\JsonResource;

class VerifyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
//            'account' => new CustomerResource(Account::find($this->account_id)),
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

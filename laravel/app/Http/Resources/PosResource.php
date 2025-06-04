<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        return [
            'id' => $this->id,
            'address' => $this->address,
            'zipcode' => $this->zipcode,
            'country' => $this->country,
            'breitling_pin' => $this->breitling_pin,
            'country_flag' => $this->country_flag,
        ];
    }
}

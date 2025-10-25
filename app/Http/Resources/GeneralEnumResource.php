<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralEnumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->value,
            'label' => $this->getLabel(),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaginatorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'meta' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total_items' => $this->total(),
                'total_pages' => $this->lastPage(),
            ],
            'links' => [
                'first_page_url' => $this->url(1),
                'prev_page_url' => $this->previousPageUrl(),
                'next_page_url' => $this->nextPageUrl(),
                'last_page_url' => $this->url($this->lastPage()),
            ],
        ];
    }
}

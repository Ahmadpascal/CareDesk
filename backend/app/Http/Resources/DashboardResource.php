<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_tickets' => $this->total_tickets,
            'active_tickets' => $this->active_tickets,
            'open_tickets' => $this->open_tickets,
            'in_progress_tickets' => $this->in_progress_tickets,
            'resolved_tickets' => $this->resolved_tickets,
            'rejected_tickets' => $this->rejected_tickets,
            'average_resolution_time' => $this->avg_resolution_time,
            'status_distribution' => [
                'open' => $this->status_distribution_open,
                'in_progress' => $this->status_distribution_in_progress,
                'resolved' => $this->status_distribution_resolved,
                'rejected' => $this->status_distribution_rejected,
            ],
        ];
    }
}

<?php

namespace App\Events;

use App\Models\Certificate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CertificateGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $certificate;

    /**
     * Create a new event instance.
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }
}
<?php

namespace App\Events;

use App\Models\Certificate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CertificateGenerated
{
    use Dispatchable, SerializesModels;

    public $certificate;

    public function __construct($certificate)
    {
        $this->certificate = $certificate;
    }
}

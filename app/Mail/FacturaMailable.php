<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FacturaMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $factura;
    public $pdfContent;

    public function __construct($factura, $pdfContent)
    {
        $this->factura = $factura;
        $this->pdfContent = $pdfContent;
    }

    public function build()
    {
        return $this
            ->subject('Factura de CH Logistics')
            ->view('emails.factura')
            ->with(['factura' => $this->factura])
            ->attachData($this->pdfContent, 'factura_'.$this->factura->id.'.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
} 
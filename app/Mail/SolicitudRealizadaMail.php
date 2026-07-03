<?php

namespace App\Mail;

use App\Models\Entidad;
use App\Models\EstadoCivil;
use App\Models\Solicitud;
use App\Models\TipoAsociado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudRealizadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $solicitud;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Solicitud $solicitud)
    {
        $this->solicitud = $solicitud;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Solicitud de Asociación Recibida',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud_realizada',
            with: [
                'solicitud' => $this->solicitud,
            ],
        );
    }


    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments(): array
    {
        $solicitud = $this->solicitud;
        $solicitud->load([
            'familiares.tipo_familia',
            'ficha_medica',
            'estado_civil',
            'ciudad',
            'tipo_vivienda',
            'departamento',
            'distrito',
        ]);
        $entidad = Entidad::find(1);
        $tiposAsociados = TipoAsociado::all();
        $civil = EstadoCivil::all();
        $pdf = Pdf::loadView('solicitud.pdf', compact('solicitud', 'entidad','tiposAsociados','civil'))
        ->setPaper('legal', 'portrait');

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                'solicitud-'.$this->solicitud->anio.'-'.$this->solicitud->numero_solicitud.'.pdf'
            )->withMime('application/pdf'),
        ];
    }
}

<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BonDeCommandeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $emailSubject,
        public string $emailBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bon-de-commande',
            with: [
                'order' => $this->order,
                'messageBody' => $this->emailBody,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        // BC signé en priorité
        if ($this->order->path_signed_purchase_order && Storage::disk('public')->exists($this->order->path_signed_purchase_order)) {
            $attachments[] = Attachment::fromStorageDisk('public', $this->order->path_signed_purchase_order)
                ->as('BC-' . $this->order->getOrderNumber() . '-SIGNE.pdf')
                ->withMime('application/pdf');
        }
        // Sinon BC original
        elseif ($this->order->path_purchase_order && Storage::disk('public')->exists($this->order->path_purchase_order)) {
            $attachments[] = Attachment::fromStorageDisk('public', $this->order->path_purchase_order)
                ->as('BC-' . $this->order->getOrderNumber() . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}

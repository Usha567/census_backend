<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
class CustomerMail extends Mailable
{
    use Queueable, SerializesModels;
    private $requestid;
    private $name;
    private $supplier;
    private $product;
    private $quantity;
    private $created;
    private $unit;
    private $usertype;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($requestid,$name,$supplier,$product,$quantity,$created,$unit,$usertype)
    {
        $this->requestid = $requestid;
        $this->name = $name;
        $this->supplier = $supplier;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->created = $created;
        $this->unit = $unit;
        $this->usertype = $usertype;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */

    public function envelope()
    {
        return new Envelope(
            subject: 'Notifications for response to customer from supplier',
        );
    }
 /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */

    public function content()
    {
        return new Content(
            view: 'email.sendQuotationMail',
            with: ['requestid' => $this->requestid,
                    'name' => $this->name,
                    'supplier' => $this->supplier,
                    'product' => $this->product,
                    'quantity' => $this->quantity,
                    'created' => $this->created,
                    'unit'=>$this->unit,
                    'usertype'=>$this->usertype
                    ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
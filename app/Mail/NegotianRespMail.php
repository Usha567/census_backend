<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NegotianRespMail extends Mailable
{
    use Queueable, SerializesModels;
    private $roleid;
    private $name;
    private $supplier;
    private $customer;
    private $responseid;
    private $created;
    private $product;
    private $usertype;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($roleid,$name,$supplier,$customer,$responseid,$created, $product, $usertype)
    {
        $this->roleid = $roleid;
        $this->name = $name;
        $this->supplier=$supplier;
        $this->customer = $customer;
        $this->responseid = $responseid;
        $this->created = $created;
        $this->product = $product;
        $this->usertype = $usertype;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        $subject = $this->usertype=='Customer'?'Supplier Response to Your Negotiations':'Confirmation: Response to Customer Negotiation';
        return new Envelope(
            subject:$subject,
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
            view: 'email.negotiationRespMail',
            with: ['roleid' => $this->roleid,
            'name' => $this->name,
            'supplier'=>$this->supplier,
            'customer' => $this->customer,
            'responseid' => $this->responseid,
            'created' => $this->created,
            'product' => $this->product,
            'usertype'=> $this->usertype
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

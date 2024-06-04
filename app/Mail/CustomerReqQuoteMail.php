<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerReqQuoteMail extends Mailable
{
    use Queueable, SerializesModels;
    private $requestid;
    private $name;
    private $customer;
    private $product;
    private $quantity;
    private $created;
    private $unit;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($requestid,  $name, $customer,$product, $quantity,  $created, $unit)
    {
        $this->requestid = $requestid;
        $this->name = $name;
        $this->customer = $customer;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->created = $created; 
        $this->unit =  $unit;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Request Confirmation',
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
            view: 'email.CustomerReqQuoteMail',
            with:[
                'requestid'=>$this->requestid,
                'name'=>$this->name,
                'customer' => $this->customer,
                'product'=> $this->product,
                'quantity'=> $this->quantity,
                'created'=>$this->created,
                'unit'=>$this->unit
            ]
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

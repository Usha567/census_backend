<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
class OrderAccept extends Mailable
{
    use Queueable, SerializesModels;
    private $roleid;
    private $name;
    private $customer;
    private $responseid;
    private $created;
    private $product;
    private $amount;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($roleid,$name,$customer,$responseid,$created, $product,$amount)
    {
        $this->roleid = $roleid;
        $this->name = $name;
        $this->customer = $customer;
        $this->responseid = $responseid;
        $this->created = $created;
        $this->product = $product;
        $this->amount = $amount;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */

    public function envelope()
    {
        $subject = $this->roleid==3?'Order Accept Confirmation Mail': 'Order Accept Notification Mail';
        return new Envelope(
            subject: $subject,
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
            view: 'email.orderAcceptMail',
            with: [
                'roleid'=>$this->roleid,
                'name' => $this->name,
                'customer' => $this->customer,
                'responseid' => $this->responseid,
                'created' => $this->created,
                'product'=>$this->product,
                'amount'=>$this->amount
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
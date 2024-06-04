<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use QrCode;
use PDF;
use File;
use Illuminate\Support\Str;

class OrderShippingBarcode extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $customerid;
    public $supplierid;
    public $product;
    public $quantity;
    public $invoiceno;
    public $delivery_date;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$customerid, $supplierid,$product,  $quantity, $invoiceno, $delivery_date)
    {
        $this->name = $name;
        $this->customerid = $customerid;
        $this->supplierid = $supplierid;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->invoiceno = $invoiceno;
        $this->delivery_date = $delivery_date;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Your Quotation Approval & QR Code',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function build()
    { 
        // Concatenate the data you want to include in the QR code
        $qrData = "customer_id - " .  $this->customerid . "\n" .
        "supplier_id - " .  $this->supplierid . "\n" .
        "product_name - " . $this->product . "\n" .
        "product_quantity - " . $this->quantity . "\n".
        "Invoice_no - " .  $this->invoiceno . "\n".
        "Delivery_date - " . $this->delivery_date;
        $svgFile = public_path('qr_code.svg'); // Path to save the SVG file

        $qrCode = QrCode::size(300)->generate($qrData, $svgFile);

        // Load the SVG content
        $svgContent = File::get($svgFile);
    

        $data = [
            'name' => $this->name,
            'customer_id'=> $this->customerid,
            'product' => $this->product,
            'quantity' => $this->quantity,
            'invoiceno'=>$this->invoiceno,
            'delivery_date'=>$this->delivery_date,
            'qrCode' => $svgContent
        ];

        $pdf = PDF::loadView('pdf.orderBarcode', $data);

        return $this->view('email.orderShippingBarcode')
                    ->with([
                        'name' => $this->name,
                        'qrCode' => $svgContent])
                    ->attachData($pdf->output(), 'qr_code.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
    public function content()
    {
        return new Content(
            view: 'email.orderShippingBarcode',
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

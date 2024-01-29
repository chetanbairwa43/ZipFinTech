<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewOrder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {    
        $data['message']=$this->data['message'];
        $mail_data = $this->from($this->data['from_email'],$this->data['name'])
        ->subject($this->data['subject']);
        $mail_data->view('mails.mail',compact('data'));
        return $mail_data;
    }
}

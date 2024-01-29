<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->data=$user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail_data = $this->from($this->data['from_email'],$this->data['name'])
        ->subject($this->data['subject']);
        
        if(!empty($this->data['attachments'])) {
            $mail_data->attach($this->data['attachments']->getRealPath() ,
            [
                'as' => $this->data['attachments']->getClientOriginalName(),
                'mime' => $this->data['attachments']->getClientMimeType(),
            ]);
        }
        $mail_data->view('mails.mail');
        return $mail_data;
    }
}

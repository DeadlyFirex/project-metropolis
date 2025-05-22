<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ModuleAangemaaktMail extends Mailable
{
    use Queueable, SerializesModels;

    public $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function build()
    {
        return $this->subject('Nieuwe module aangemaakt')
                    ->view('emails.module_aangemaakt');
    }
}

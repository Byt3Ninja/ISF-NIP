<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class ExportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $filePath;
    public $fileName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($filePath, $fileName)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->view('admin.emails.email') // اسم العرض
        ->subject('Your Export File') // عنوان البريد الإلكتروني
        ->attach($this->filePath, [
            'as' => $this->fileName, // اسم المرفق في البريد
            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // نوع MIME للملف
                    ]);


                    
    }
}

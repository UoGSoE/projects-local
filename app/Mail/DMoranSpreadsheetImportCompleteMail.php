<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class DMoranSpreadsheetImportCompleteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $errors = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $errorSetName)
    {
        $this->errors = Redis::smembers($errorSetName);
        Redis::del($errorSetName);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.dmoran_import_complete');
    }
}

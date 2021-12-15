<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\mWork\Core;

class UserCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user=$user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {      
        return $this->view('userCreated',[
            'user_name'=>strtoupper($this->user->name),
            'user_type'=> strtoupper(Core::getConstant($this->user->role)),
            'user_email'=> strtoupper($this->user->email),
            'user_password'=>$this->user->plain_password,
            'url'=>Core::getLabel('LOGIN_URL')
        ])->to(Core::getLabel('EMAIL_FOR_SENDING_DEFAULT_PASSWORD'));  
    }
}

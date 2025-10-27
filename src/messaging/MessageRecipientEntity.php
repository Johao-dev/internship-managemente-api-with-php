<?php

namespace App\messaging;

class MessageRecipientEntity {

    public $id;
    public $message_id;
    public $user_id;
    public $readed;
    public $read_date;
}
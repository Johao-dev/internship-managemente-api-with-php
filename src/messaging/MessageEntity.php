<?php

namespace App\messaging;

class MessageEntity {

    public $id;
    public $title;
    public $content;
    public $remitent_id;
    public $recipient_type;
    public $send_date;
    public $active;
    public $created_at;
}
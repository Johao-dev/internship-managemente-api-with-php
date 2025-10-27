<?php

namespace App\messaging;

enum RecipientType: String {
    case INTERN = "intern";
    case SUPERVISOR = "supervisor";
    case ALL = "all";
}

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
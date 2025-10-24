<?php

namespace App\messaging;

enum RecipientType: String {
    case INTERN = "intern";
    case SUPERVISOR = "supervisor";
    case ALL = "all";
}

class MessageEntity {

    private $id;
    private $title;
    private $content;
    private $remitent_id;
    private $recipient_type;
    private $send_date;
    private $active;
    private $created_at;
}
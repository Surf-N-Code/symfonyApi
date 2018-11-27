<?php
/**
 * Created by PhpStorm.
 * User: n.dilthey
 * Date: 2018-11-26
 * Time: 18:01
 */

namespace App\Messages;

use App\Entity\Message;

class RabbitMessage
{
    private $messageData;

    public function __construct(Message $messageData)
    {
        $this->messageData = $messageData;
    }

    public function getMessageData()
    {
        return $this->messageData;
    }
}
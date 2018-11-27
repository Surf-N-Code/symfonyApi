<?php
/**
 * Created by PhpStorm.
 * User: n.dilthey
 * Date: 2018-11-26
 * Time: 18:02
 */

namespace App\MessageHandler;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Messages\RabbitMessage;

class MessageHandler implements MessageHandlerInterface
{
    private $manager;
    private $logger;

    public function __construct(ObjectManager $manager, LoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->logger = $logger;
    }

    public function __invoke(RabbitMessage $message)
    {
        try {
            $passedMessage = $message->getMessageData();

            $postedOn = $passedMessage->getPostedOn();
            $format = 'Y-m-d\TH:i:sP';
            $timezone = new \DateTimeZone('Europe/Amsterdam');
            $dt = \DateTimeImmutable::createFromFormat($format, $postedOn, $timezone);
            $passedMessage->setPostedOn($dt);

            $this->manager->persist($passedMessage);
            $this->manager->flush();
            echo "message handled";
            $this->logger->info(
                "Added message to DB with ID: ".$passedMessage->getId()
            );
        } catch(ORMException $e) {
            $this->logger->error(
                "Failed adding message to database". json_decode($e)
            );
        }
    }
}
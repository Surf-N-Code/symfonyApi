<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Message;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraints\DateTime;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $messages = [
            'Hallo liebes Yazio Team',
            'Die Aufgabe macht Spaß :)',
            'Ich hoffe es folgen noch viele weitere Aufgaben',
            'Bis bald mal in Erfurt!'
        ];

        $comments = [
            'Hallo Norman',
            'Symfony Projekte sind cool',
        ];

        $messageDates = [
            'PT1H',
            'PT8H',
            'P2D',
            'P3D',
        ];

        // create 20 products! Bam!
        for ($i = 0; $i < count($messages); $i++) {
            $message = new Message();
            $message->setContent($messages[$i]);
            $message->setTitle("Nachrichtentitel - ".$i);
            $message->setViews($i);
            $date = new \DateTime(date('Y-m-d H:i:s'));
            $date->sub(new \DateInterval($messageDates[$i]));
            $message->setPostedOn($date);

            if(isset($comments[$i])) {
                $comment = new Comment();
                $comment->setComment($comments[$i]);
                $message->addComment($comment);
            }

            $manager->persist($message);
        }

        $manager->flush();
    }
}

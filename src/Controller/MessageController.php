<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class MessageController
 * @package App\Controller
 */
class MessageController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var MessageRepository
     */
    private $messageRepo;

    /**
     * MessageController constructor.
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     * @param MessageRepository $messageRepo
     */
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, MessageRepository $messageRepo) {
        $this->em = $em;
        $this->logger = $logger;
        $this->messageRepo = $messageRepo;
    }

    /**
     * @param $id
     * @return Message|null
     */
    private function findMessageById($id) {
        $message = $this->messageRepo->find($id);
        if($message === null) {
            throw new NotFoundHttpException();
        }
        return $message;
    }

    /**
     * @param Message $message
     */
    private function incrementViews(Message $message) {
        $message->setViews($message->getViews()+1);
        $this->em->persist($message);
        $this->em->flush();
    }

    private function isPostedInLast24Hours(Message $message) {
        $now = new \DateTime('now');
        $dayAgo = $now->modify('-1 day');
        $messDate = new \DateTime($message->getPostedOn());
        return ($messDate >= $dayAgo) ? true : false;
    }

    /**
     * @param Request $request
     * @return View
     */
    public function postAction(Request $request) {
        $data = json_decode(
            $request->getContent(),
        true
        );

        $form = $this->createForm(MessageType::class, new Message());
        $form->submit($request->request->all());
        if($form->isSubmitted() && !$form->isValid()) {
            return $this->view($form);
        }

        try {
            $this->em->persist($form->getData());
            $this->em->flush();
        } catch(ORMException $e) {
            $this->logger->error(
                "Failed adding message to database". json_decode($e)
            );
        }

        return $this->view([
            'status' => 'Message added successfully',
            ],
            Response::HTTP_CREATED
        );
    }



    /**
     * @param string $id
     * @return View
     */
    public function getAction(string $id) {

        $message = $this->findMessageById($id);
        if($this->isPostedInLast24Hours($message)) {
            $this->incrementViews($message);
        }

        return $this->view(
            $message
        );
    }

    /**
     * @return View
     */
    public function cgetAction() {
        return $this->view(
            array_filter(
                $this->messageRepo->findAll(),
                function(Message $message) {
                    if($this->isPostedInLast24Hours($message)) {
                        $this->incrementViews($message);
                        return $message;
                    }
                }
            )
        );
    }

    /**
     * @param Request $request
     * @param string $id
     * @return View
     */
    public function patchAction(Request $request, string $id) {
        $existingMessage = $this->findMessageById($id);
        $form = $this->createForm(MessageType::class, $existingMessage);
        $form->submit($request->request->all(), false);

        if (false === $form->isValid()) {
            return $this->view($form);
        }

        $this->em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     * @return View
     */
    public function deleteAction(string $id) {
        $message = $this->findMessageById($id);

        $this->em->remove($message);
        $this->em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}

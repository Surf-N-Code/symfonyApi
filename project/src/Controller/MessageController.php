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
use Symfony\Component\Messenger\MessageBusInterface;
use App\Messages\RabbitMessage;

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
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * MessageController constructor.
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     * @param MessageRepository $messageRepo
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        MessageRepository $messageRepo,
        MessageBusInterface $messageBus) {
        $this->em = $em;
        $this->logger = $logger;
        $this->messageRepo = $messageRepo;
        $this->messageBus = $messageBus;
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

        $message = new Message();
        $this->messageBus->dispatch(new RabbitMessage($form->getData()));

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

        $message = $this->messageRepo->findByIdAndPostedInLast24Hours($id);
        if($message === null || count($message) == 0) {
            throw new NotFoundHttpException();
        }

        $this->incrementViews($message[0]);

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
                $this->messageRepo->findPostedInLast24Hours(),
                function(Message $message) {
                    $this->incrementViews($message);
                    return $message;
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

        try {
            $this->em->flush();
        } catch (ORMException $e) {
            $this->logger("Failed patching message with ID: ".$id.". Message: ".json_encode($e));
        }


        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     * @return View
     */
    public function deleteAction(string $id) {
        $message = $this->findMessageById($id);

        //@TODO: Could also be handled in rabbit
        try {
            $this->em->remove($message);
            $this->em->flush();
        } catch (ORMException $e) {
            $this->logger("Failed deleting message with ID: ".$id.". Message: ".json_encode($e));
        }


        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param $id
     * @return Message|null
     * @throws NotFoundHttpException
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
    private function incrementViews($message) {
        $message->setViews($message->getViews()+1);
        //@TODO: Could also be handled in rabbit
        try {
            $this->em->persist($message);
            $this->em->flush();
        } catch (ORMException $e) {
            $this->logger("Failed updating views on message with ID: ".$message->getId().". Message: ".json_encode($e));
        }
    }
}

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
     * @param EntityManager $em
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
                "Failed adding meessage to database". json_decode($e)
            );
        }

        return $this->view([
            'status' => 'ok',
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * @param string $id
     * @return View
     */
    public function getAction(string $id) {
        return $this->view($this->findMessageById($id));
    }

    /**
     * @return View
     */
    public function cgetAction()
    {
        return $this->view(
            $this->messageRepo->findAll()
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

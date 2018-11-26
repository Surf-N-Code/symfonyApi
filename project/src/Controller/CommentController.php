<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Message;
use App\Form\CommentType;
use App\Repository\CommentRepository;
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
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class CommentController
 * @package App\Controller
 */
class CommentController extends FOSRestController implements ClassResourceInterface
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
     * @var CommentRepository
     */
    private $commentRepo;

    /**
     * @var MessageRepository
     */
    private $messageRepo;

    /**
     * CommentController constructor.
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     * @param CommentRepository $commentRepo
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        CommentRepository $commentRepo,
        MessageRepository $messageRepo) {
        $this->em = $em;
        $this->logger = $logger;
        $this->commentRepo = $commentRepo;
        $this->messageRepo = $messageRepo;
    }

    /**
     * @param $id
     * @return Comment|null
     */
    private function findCommentById($id) {
        $comment = $this->commentRepo->find($id);
        if($comment === null) {
            throw new NotFoundHttpException();
        }
        return $comment;
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
    public function postAction(Request $request, string $messageId) {
        $data = json_decode(
            $request->getContent(),
            true
        );

        $form = $this->createForm(CommentType::class, new Comment());
        $form->submit($request->request->all());
        if($form->isSubmitted() && !$form->isValid()) {
            return $this->view($form);
        }
        $comment = $form->getData();
        $message = $this->findMessageById($messageId);
        $message->addComment($comment);

        try {
            $this->em->persist($form->getData());
            $this->em->flush();
        } catch(ORMException $e) {
            $this->logger->error(
                "Failed adding comment to database". json_decode($e)
            );
        }

        return $this->view([
            'status' => 'Comment added successfully',
        ],
            Response::HTTP_CREATED
        );
    }

    /**
     * @Rest\View(serializerGroups={"private"})
     * @param string $id
     * @return View
     */
    public function getAction(string $id) {
        $comment = $this->findCommentById($id);
        $comment->getComment();
        return $this->view($comment);
    }

    /**
     * @Rest\View(serializerGroups={"private"})
     * @return View
     */
    public function cgetAction() {
        return $this->view(
            array_map(function($comment) {
                return $comment->getComment();
            }, $this->commentRepo->findAll())
        );
    }

    /**
     * @Rest\View(serializerGroups={"private"})
     * @param string $id
     * @return View
     */
    public function deleteAction(string $id) {
        $comment = $this->findCommentById($id);

        $this->em->remove($comment);
        $this->em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}

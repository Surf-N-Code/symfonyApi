<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid")
     * @Groups({"public"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"public"})
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 1,
     *     max = 254,
     *     minMessage = "Please enter at least {{ limit }} characters in your comment",
     *     maxMessage = "Please limit your comment to {{ limit }} characters"
     * )
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Message", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"private"})
     */
    private $message;


    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return Comment
     */
    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Message|null
     */
    public function getMessage(): ?Message
    {
        return $this->message;
    }

    /**
     * @param Message|null $message
     * @return Comment
     */
    public function setMessage(?Message $message): self
    {
        $this->message = $message;

        return $this;
    }
}

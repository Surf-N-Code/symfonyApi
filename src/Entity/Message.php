<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 */
class Message
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $content;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default":0})
     */
    private $views;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $postedOn;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="message", orphanRemoval=true)
     */
    private $comments;

    /**
     * Message constructor.
     */
    public function __construct()
    {
        $this->postedOn = new \DateTime('now');
        $this->comments = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Message
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return Message
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return integer
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @param integer $views
     */
    public function setViews($views): void
    {
        $this->views = $views;
    }

    /**
     * @return DateTime
     */
    public function getPostedOn()
    {
        return $this->postedOn;
    }

    /**
     * @param DateTime $postedOn
     */
    public function setPostedOn($postedOn): void
    {
        $this->postedOn = $postedOn;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @param Comment $comment
     * @return Message
     */
    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setMessage($this);
        }

        return $this;
    }

    /**
     * @param Comment $comment
     * @return Message
     */
    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getMessage() === $this) {
                $comment->setMessage(null);
            }
        }

        return $this;
    }
}

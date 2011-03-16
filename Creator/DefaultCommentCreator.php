<?php

namespace FOS\CommentBundle\Creator;

use Symfony\Component\HttpFoundation\Request;
use FOS\CommentBundle\Model\CommentManagerInterface;
use FOS\CommentBundle\Model\CommentInterface;
use FOS\CommentBundle\Blamer\CommentBlamerInterface;
use FOS\CommentBundle\Akismet;

/**
 * @see CommentCreatorInterface
 */
class DefaultCommentCreator implements CommentCreatorInterface
{
    protected $request;
    protected $commentManager;
    protected $commentBlamer;
    protected $akismet;

    public function __construct(Request $request, CommentManagerInterface $commentManager, CommentBlamerInterface $commentBlamer, Akismet $akismet = null)
    {
        $this->request        = $request;
        $this->commentManager = $commentManager;
        $this->commentBlamer  = $commentBlamer;
        $this->akismet        = $akismet;
    }

    public function create(CommentInterface $comment)
    {
        $this->commentBlamer->blame($comment);

        if ($this->akismet && $this->akismet->isSpam($comment)) {
            return false;
        }

        $parent = $this->commentManager->findCommentById($this->request->request->get('reply_to'));

        $this->commentManager->addComment($comment, $parent);

        return true;
    }
}
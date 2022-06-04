<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CommentService
{
    public function __construct(
        private TranslatorInterface $translator,
        private EntityManagerInterface $em,
    ) {
    }

    public function update(Comment $comment, Request $request): void
    {
        $comment->setText($request->query->get('text'));
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('notice', $this->translator->trans('alerts.comment.updated'));
    }

    public function delete(Comment $comment, Request $request): void
    {
        $this->em->remove($comment);
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('notice', $this->translator->trans('alerts.comment.deleted'));
    }
}
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Movie;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_USER')]
#[Route('/{_locale<%app.supported_locales%>}/{movie}/comments', name: 'comments_')]
final class CommentController extends AbstractController
{
    public function __construct(
        public CommentRepository $commentRepository,
        private TranslatorInterface $translator,
        public EntityManagerInterface $em,
    ) {}

    #[Route('/create', name: 'create')]
    public function create(Movie $movie, Request $request): RedirectResponse
    {
        $comment = new Comment();
        $comment->setAuthor($this->getUser());
        $comment->setMovie($movie);
        $comment->setText($request->get('text'));

        $this->commentRepository->add($comment);

        return $this->redirectToRoute('movies_show', ['movie' => $movie->getId()]);
    }

    #[IsGranted('COMMENT_DELETE', 'comment')]
    #[Route('/{comment}/delete', name: 'delete', methods: ['DELETE'])]
    public function delete(Movie $movie, Comment $comment): RedirectResponse
    {
        $this->em->remove($comment);
        $this->em->flush();
        $this->addFlash('notice', $this->translator->trans('alerts.comment.deleted'));

        return $this->redirectToRoute('movies_show', ['movie' => $movie->getId()]);
    }
}

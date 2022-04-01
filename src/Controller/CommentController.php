<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Movie;
use App\Repository\CommentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/{_locale<%app.supported_locales%>}/{movie}/comments', name: 'comments_')]
class CommentController extends AbstractController
{
    public function __construct(public CommentRepository $commentRepository) {}

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    #[Route('/create', name: 'create')]
    #[IsGranted('ROLE_USER')]
    public function create(Movie $movie, Request $request): RedirectResponse
    {
        $comment = new Comment();
        $comment->setAuthor($this->getUser());
        $comment->setMovie($movie);
        $comment->setText($request->get('text'));

        $this->commentRepository->add($comment);

        return $this->redirectToRoute('movies_show', ['id' => $movie->getId()]);
    }
}

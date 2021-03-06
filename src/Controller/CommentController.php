<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Movie;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Service\CommentService;
use DateTimeImmutable;
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
        private CommentRepository $commentRepository,
        private CommentService $commentService,
        private TranslatorInterface $translator,
    ) {}

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Movie $movie, Request $request): RedirectResponse
    {
        $commentsCount = $this->commentRepository->getCommentsCount($this->getUser(), $movie);

        if ($commentsCount >= Comment::MAX_AMOUNT) {
            $this->addFlash('notice', $this->translator->trans('alerts.comment.timeout'));

            return $this->redirectToRoute('movies_show', ['movie' => $movie->getId()]);
        }

        $comment = new Comment();

        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setAuthor($this->getUser());
            $comment->setMovie($movie);
            $comment->setCreatedAt(new DateTimeImmutable('now'));

            $this->commentRepository->add($comment);
        }

        return $this->redirectToRoute('movies_show', ['movie' => $movie->getId()]);
    }

    #[IsGranted('COMMENT_UPDATE', 'comment')]
    #[Route('/{comment}/update', name: 'update', methods: ['PUT'])]
    public function update(Movie $movie, Comment $comment, Request $request): RedirectResponse
    {
        $submittedToken = $request->request->get('token');

        if ($this->isCsrfTokenValid('update-comment', $submittedToken)) {
            $form = $this->createForm(CommentFormType::class, $comment);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->commentService->update($comment, $request);

                $this->commentRepository->add($comment);
            }

            return $this->redirectToRoute('movies_show', ['movie' => $movie->getId()]);
        }

        return $this->redirectToRoute('homepage');
    }

    #[IsGranted('COMMENT_DELETE', 'comment')]
    #[Route('/{comment}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Movie $movie, Comment $comment, Request $request): RedirectResponse
    {
        $submittedToken = $request->request->get('token');

        if ($this->isCsrfTokenValid('delete-comment', $submittedToken)) {
            $this->commentService->delete($comment, $request);

            return $this->redirectToRoute('movies_show', ['movie' => $movie->getId()]);
        }

        return $this->redirectToRoute('homepage');
    }
}

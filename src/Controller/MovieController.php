<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Movie;
use App\Form\CommentFormType;
use App\Form\MovieFormType;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\MovieRepository;
use App\Service\MovieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale<%app.supported_locales%>}/movies', name: 'movies_')]
final class MovieController extends AbstractController
{
    public function __construct(
        private MovieRepository $movieRepository,
        private CommentRepository $commentRepository,
        private CategoryRepository $categoryRepository,
        private TranslatorInterface $translator,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $this->movieRepository->getMoviesPaginator($offset);

        return $this->render('movie/list.html.twig', [
            'movies' => $paginator,
            'previous' => $offset - MovieRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + MovieRepository::PAGINATOR_PER_PAGE)
        ]);
    }

    #[Route('/{movie<\d+>}', name: 'show', methods: ['GET'])]
    public function show(Movie $movie, Request $request): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment, [
            'action' => $this->generateUrl('comments_create', ['movie' => $movie->getId()]),
            'method' => 'POST',
        ]);

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $this->commentRepository->getCommentPaginator($movie, $offset);

        return $this->renderForm('movie/show.html.twig', [
            'movie' => $movie,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, MovieService $movieService): Response
    {
        $categories = $this->categoryRepository->findAll();
        $movie = new Movie();

        $form = $this->createForm(MovieFormType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imagePath = $form->get('image')->getData();
            if ($imagePath) {
                $movieService->storeImage($movie, $imagePath);
            }

            $this->movieRepository->add($movie);
            $this->addFlash('notice', $this->translator->trans('alerts.movie.created'));

            return $this->redirectToRoute('movies_list');
        }

        return $this->renderForm('movie/create.html.twig', [
            'categories' => $categories,
            'form' => $form
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{movie<\d+>}/update', name: 'update', methods: ['GET', 'POST'])]
    public function update(Movie $movie, Request $request, MovieService $movieService): Response
    {
        $form = $this->createForm(MovieFormType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imagePath = $form->get('image')->getData();

            if ($imagePath) {
                $movieService->storeImage($movie, $imagePath);
            }

            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('alerts.movie.updated'));

            return $this->redirectToRoute('movies_list');
        }

        return $this->renderForm('movie/update.html.twig', [
            'movie' => $movie,
            'form' => $form
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{movie<\d+>}/delete', name: 'delete', methods: ['DELETE'])]
    public function delete(Movie $movie): Response
    {
        $this->em->remove($movie);
        $this->em->flush();
        $this->addFlash('notice', $this->translator->trans('alerts.movie.deleted'));

        return $this->redirectToRoute('movies_list');
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieFormType;
use App\Repository\MovieRepository;
use App\Service\MovieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/movies', name: 'movies_')]
class MovieController extends AbstractController
{
    public function __construct(
        private MovieRepository $movieRepository,
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

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, MovieService $movieService): Response
    {
        $movie = new Movie();
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imagePath = $form->get('image')->getData();

            if ($imagePath) {
                $movieService->storeImage($movie, $imagePath);
            }

            $this->em->persist($movie);
            $this->em->flush();

            $this->addFlash('notice', 'You have successfully created a movie');

            return $this->redirectToRoute('movies_list');
        }

        return $this->renderForm('movie/create.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/update', name: 'update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(Movie $movie, Request $request, MovieService $movieService): Response
    {
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);
        $imagePath = $form->get('image')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($imagePath) {
                $movieService->storeImage($movie, $imagePath);
            }

            $this->em->flush();

            $this->addFlash('notice', 'You have successfully updated a movie');

            return $this->redirectToRoute('movies_list');
        }

        return $this->renderForm('movie/update.html.twig', [
            'movie' => $movie,
            'form' => $form
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'], methods: ['GET', 'DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Movie $movie): Response
    {
        $this->em->remove($movie);
        $this->em->flush();

        return $this->redirectToRoute('movies_list');
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Movie $movie): Response
    {
        return $this->render('movie/show.html.twig', ['movie' => $movie]);
    }
}

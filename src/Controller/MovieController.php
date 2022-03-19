<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Core\Validator\Exception\ValidationException;
use App\Entity\Movie;
use App\Form\MovieUpdateFormType;
use App\Repository\CategoryRepository;
use App\Repository\MovieRepository;
use App\Service\MovieService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/movies', name: 'movies_')]
class MovieController extends AbstractController
{
    public function __construct(
        private MovieRepository $movieRepository,
        private CategoryRepository $categoryRepository,
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

    #[Route('/create', name: 'create', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(): Response
    {
        $categories = $this->categoryRepository->findAll();

        return $this->render('movie/create.html.twig', ['categories' => $categories]);
    }

    #[Route('/store', name: 'store', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function store(Request $request, MovieService $movieService, LoggerInterface $logger, ValidatorInterface $validator): Response
    {
        $movie = new Movie();

        $token = $request->get("token");
        if (!$this->isCsrfTokenValid('store', $token))
        {
            $logger->info("CSRF failure");

            return new Response("Operation not allowed", Response::HTTP_BAD_REQUEST);
        }

        $movie->setTitle($request->get('title'));
        $movie->setRating((float)$request->get('rating'));
        $movie->setDescription($request->get('description'));
        $category = $this->categoryRepository->find($request->get('category'));
        $movie->setCategory($category);

        $imagePath = $request->files->get('image');
        if ($imagePath) {
            $movieService->storeImage($movie, $imagePath);
        }

        $errors = $validator->validate($movie);
        if (count($errors) > 0) {
            throw new ValidationException((string)$errors);
        }

        $this->em->persist($movie);
        $this->em->flush();
        $this->addFlash('notice', 'You have successfully created a movie');

        return $this->redirectToRoute('movies_list');
    }

    #[Route('/{id}/update', name: 'update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(Movie $movie, Request $request, MovieService $movieService): Response
    {
        $form = $this->createForm(MovieUpdateFormType::class, $movie);

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
        $this->addFlash('notice', 'You have successfully deleted a movie');

        return $this->redirectToRoute('movies_list');
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Movie $movie): Response
    {
        return $this->render('movie/show.html.twig', ['movie' => $movie]);
    }
}

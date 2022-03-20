<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieUpdateFormType;
use App\Repository\CategoryRepository;
use App\Repository\MovieRepository;
use App\Service\MovieService;
use App\Service\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}/movies', name: 'movies_', requirements: ['_locale' => 'en|fr'])]
class MovieController extends AbstractController
{
    public function __construct(
        private MovieRepository $movieRepository,
        private CategoryRepository $categoryRepository,
        private MovieService $movieService,
        private ValidationService $validationService,
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

    #[Route('/create', name: 'create', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(): Response
    {
        $categories = $this->categoryRepository->findAll();

        return $this->render('movie/create.html.twig', [
            'categories' => $categories,
            'errorMessages' => $this->movieService->getMovieFields(),
        ]);
    }

    #[Route('/store', name: 'store', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function store(Request $request, LoggerInterface $logger, ValidatorInterface $validator): Response
    {
        $token = $request->get("token");
        if (!$this->isCsrfTokenValid('store', $token))
        {
            $logger->info("CSRF failure");

            return new Response("Operation not allowed", Response::HTTP_BAD_REQUEST);
        }

        $movie = new Movie();
        $imagePath = $request->files->get('image');
        $category = $this->categoryRepository->find($request->get('category'));
        if (!$category) {
            throw $this->createNotFoundException('No category found for id ' . $request->get('category'));
        }

        $movie->setTitle($request->get('title'));
        $movie->setRating((float)$request->get('rating'));
        $movie->setDescription($request->get('description'));
        $movie->setCategory($category);
        if ($imagePath) {
            $movie->setImage($imagePath->getPathName());
        }

        $errors = $validator->validate($movie);
        if (count($errors) > 0) {
            return $this->render('movie/create.html.twig', [
                'errorMessages' => $this->validationService->getErrorMessages($errors, $this->movieService->getMovieFields()),
                'categories' => $this->categoryRepository->findAll(),
            ]);
        }

        if ($imagePath) {
            $this->movieService->storeImage($movie, $imagePath);
        }

        $this->em->persist($movie);
        $this->em->flush();
        $this->addFlash('notice', $this->translator->trans('alerts.created'));

        return $this->redirectToRoute('movies_list');
    }

    #[Route('/{id}/update', name: 'update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(Movie $movie, Request $request, MovieService $movieService): Response
    {
        $form = $this->createForm(MovieUpdateFormType::class, $movie);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imagePath = $form->get('image')->getData();
            if ($imagePath) {
                $movieService->storeImage($movie, $imagePath);
            }

            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('alerts.updated'));

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
        $this->addFlash('notice', $this->translator->trans('alerts.deleted'));

        return $this->redirectToRoute('movies_list');
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Movie $movie): Response
    {
        return $this->render('movie/show.html.twig', ['movie' => $movie]);
    }
}

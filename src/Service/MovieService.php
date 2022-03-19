<?php

declare(strict_types=1);

namespace App\Service;

use ApiPlatform\Core\Validator\Exception\ValidationException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Image;
use App\Entity\Movie;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;

class MovieService
{
    public function __construct(
        private SluggerInterface $slugger,
        private ValidatorInterface $validator,
        private string $projectDir
    ) {}

    public function storeImage(Movie $movie, UploadedFile $imagePath)
    {
        $errors = $this->validator->validate($imagePath, new Image([
            'maxSize' => "10M",
            'minWidth' => 200,
            'maxWidth' => 5000,
            'minHeight' => 200,
            'maxHeight' => 5000,
            'mimeTypes' => [
                "image/jpeg",
                "image/jpg",
                "image/png",
                "image/gif",
            ],
        ]));
        if (count($errors)) {
            throw new ValidationException((string)$errors);
        }

        $originalFilename = pathinfo($imagePath->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-'. uniqid() . '.' . $imagePath->guessExtension();

        try {
            $imagePath
                ->move($this->projectDir, $fileName);
        } catch (FileException $e) {
            new Response($e->getMessage());
        }

        $movie->setImage($fileName);
    }

    public function getMovieFields(): array
    {
        return [
            'title' => null,
            'category' => null,
            'rating' => null,
            'description' => null,
            'image' => null
        ];
    }

    public function getErrorMessages(ConstraintViolationListInterface $errors): array
    {
        $errorMessages = $this->getMovieFields();

        foreach ($errors as $violation) {
            $errorMessages[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $errorMessages;
    }
}
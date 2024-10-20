<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry; // Import ManagerRegistry

class BookController extends AbstractController
{
    private $doctrine;

    // Inject ManagerRegistry into the controller
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

    #[Route('/AfficheBook', name: 'app_AfficheBook')]
    public function Affiche(BookRepository $repository): Response
    {
        // Retrieve published books
        $publishedBooks = $repository->findBy(['published' => true]);
        // Count the number of published and unpublished books
        $numPublishedBooks = count($publishedBooks);
        $numUnPublishedBooks = count($repository->findBy(['published' => false]));

        if ($numPublishedBooks > 0) {
            return $this->render('book/Affiche.html.twig', [
                'publishedBooks' => $publishedBooks,
                'numPublishedBooks' => $numPublishedBooks,
                'numUnPublishedBooks' => $numUnPublishedBooks
            ]);
        } else {
            // Show a message if no books were found
            return $this->render('book/no_books_found.html.twig');
        }
    }

    #[Route('/AddBook', name: 'app_AddBook')]
    public function Add(Request $request): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->add('Ajouter', SubmitType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Increment the number of books for the associated author
            $author = $book->getAuthor();
            if ($author instanceof Author) {
                $author->setNbBooks($author->getNbBooks() + 1);
            }

            $em = $this->doctrine->getManager(); // Use injected ManagerRegistry
            $em->persist($book);
            $em->flush();
            return $this->redirectToRoute('app_AfficheBook');
        }
        
        return $this->render('book/Add.html.twig', ['f' => $form->createView()]);
    }

    #[Route('/editbook/{ref}', name: 'app_editBook')]
    public function edit(BookRepository $repository, $ref, Request $request): Response
    {
        $book = $repository->find($ref);
        $form = $this->createForm(BookType::class, $book);
        $form->add('Edit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->doctrine->getManager(); // Use injected ManagerRegistry
            $em->flush(); // Save changes to the database
            return $this->redirectToRoute("app_AfficheBook");
        }

        return $this->render('book/edit.html.twig', [
            'f' => $form->createView(),
        ]);
    }

    #[Route('/deletebook/{ref}', name: 'app_deleteBook')]
    public function delete($ref, BookRepository $repository): Response
    {
        $book = $repository->find($ref);

        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $em = $this->doctrine->getManager(); // Use injected ManagerRegistry
        $em->remove($book);
        $em->flush();

        return $this->redirectToRoute('app_AfficheBook');
    }

    #[Route('/ShowBook/{ref}', name: 'app_detailBook')]
    public function showBook($ref, BookRepository $repository): Response
    {
        $book = $repository->find($ref);
        if (!$book) {
            return $this->redirectToRoute('app_AfficheBook');
        }

        return $this->render('book/show.html.twig', ['b' => $book]);
    }
}

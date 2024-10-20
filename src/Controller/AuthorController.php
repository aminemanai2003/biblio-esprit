<?php

namespace App\Controller;

use App\Form\AuthorType;
use App\Entity\Author;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class AuthorController extends AbstractController
{
    private $entityManager;

    // Inject EntityManagerInterface into the controller
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/author', name: 'app_author')]
    public function index(): Response
    {
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
        ]);
    }

    #[Route('/showAuthor/{name}', name: 'app_showAuthor')]
    public function showAuthor($name): Response
    {
        return $this->render('author/show.html.twig', ['n' => $name]);
    }

    #[Route('/showlist', name: 'app_showlist')]
    public function list(): Response
    {
        $authors = [
            ['id' => 1, 'picture' => '/images/Victor-Hugo.jpg', 'username' => 'Victor Hugo', 'email' => 'victor.hugo@gmail.com', 'nb_books' => 100],
            ['id' => 2, 'picture' => '/images/william-shakespeare.jpg', 'username' => 'William Shakespeare', 'email' => 'william.shakespeare@gmail.com', 'nb_books' => 200],
            ['id' => 3, 'picture' => '/images/Taha_Hussein.jpg', 'username' => 'Taha Hussein', 'email' => 'taha.hussein@gmail.com', 'nb_books' => 300],
        ];

        return $this->render("author/list.html.twig", ['authors' => $authors]);
    }

    #[Route('/auhtorDetails/{id}', name: 'app_authorDetails')]
    public function authorDetails($id): Response
    {
        $author = [
            'id' => $id,
            'picture' => '~images',
            'username' => 'Author',
            'email' => 'author.email',
            'nb_books' => 10,
        ];

        return $this->render("author/showAuthor.html.twig", ['author' => $author]);
    }

    #[Route('/Affiche', name: 'app_Affiche')]
    public function Affiche(AuthorRepository $repository): Response
    {
        $authors = $repository->findAll(); // select *
        return $this->render('author/Affiche.html.twig', ['author' => $authors]);
    }

    #[Route('/Add', name: 'app_Add')]
    public function Add(Request $request): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $form->add('Ajouter', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityManager;
            $em->persist($author);
            $em->flush();
            return $this->redirectToRoute('app_Affiche');
        }

        return $this->render('author/Add.html.twig', ['f' => $form->createView()]);
    }

    #[Route('/edit/{id}', name: 'app_edit')]
    public function edit(AuthorRepository $repository, $id, Request $request): Response
    {
        $author = $repository->find($id);
        $form = $this->createForm(AuthorType::class, $author);
        $form->add('Edit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityManager;
            $em->flush();
            return $this->redirectToRoute('app_Affiche');
        }

        return $this->render('author/edit.html.twig', [
            'f' => $form->createView(),
        ]);


    }

    #[Route('/delete/{id}', name: 'app_delete')]
    public function delete($id, AuthorRepository $repository): Response
    {
        $author = $repository->find($id);

        if (!$author) {
            throw $this->createNotFoundException('Auteur non trouvé');
        }

        $this->entityManager->remove($author);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_Affiche');
    }

    #[Route('/AddStatistique', name: 'app_AddStatistique')]
    public function addStatistique(): Response
    {
        // Create an instance of the Author entity
        $author1 = new Author();
        $author1->setUsername("test");
        $author1->setEmail("test@gmail.com");

        // Save the entity to the database
        $this->entityManager->persist($author1);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_Affiche'); // Redirect to the 'app_Affiche' route
    }
}

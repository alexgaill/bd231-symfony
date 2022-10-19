<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/post', name:"post_", methods:['GET'])]
class PostController extends AbstractController
{
    private const REDIRECT = "post_app";
    private const UPLOAD = "post_upload";

    private PostRepository $repository;

    public function __construct(
        private ManagerRegistry $manager
    ){
        $this->repository = $manager->getRepository(Post::class);
    }

    #[Route('/', name: 'app')]
    public function index(): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $this->repository->findAll()
        ]);
    }

    #[Route('/{id}', name:'show', requirements:['id' => "\d+"])]
    public function show(int $id): Response
    {
        $post = $this->repository->find($id);
        if (!$post) {
            $this->addFlash('danger', "L'article que vous recherchez n'existe pas.");

            return $this->redirectToRoute(self::REDIRECT);
        }

        return $this->render("post/show.html.twig", [
            'post' => $post
        ]);
    }

    #[Route('/add', name:'add', methods:['GET', 'POST'])]
    public function add(Request $request): Response
    {
        $post = new Post;
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les informations de l'image sur notre formulaire
            // Un objet UploadedFile est retourné
            $picture = $form->get('picture')->getData();
            if ($picture) {
                try {
                    $pictureName = md5(uniqid()). '.' . $picture->guessExtension();
                    // Permet de déplacer l'image dans un dossier d'upload
                    // move() prend 2 paramètres: le dossier d'upload et le nom du fichier
                    $picture->move(
                        $this->getParameter(self::UPLOAD),
                        $pictureName
                    );
                    $post->setPicture($pictureName);
                } catch (FileException $e) {
                    $this->addFlash('danger', "Une erreur s'est produite lors de l'enregistrement de l'image avec le message suivant: ". $e->getMessage());
                }
            }

            $post->setCreatedAt(new DateTime());
            $this->repository->save($post, true);
            $this->addFlash('success', "L'article a été ajouté.");

            return $this->redirectToRoute(self::REDIRECT);
        }

        return $this->renderForm("post/add.html.twig", [
            'form' => $form
        ]);
    }

    #[Route('/{id}/update', name:'update', methods:['GET', 'POST'], requirements:['id' => "\d+"])]
    public function update(int $id, Request $request): Response
    {
        $post = $this->repository->find($id);
        if (!$post) {
            $this->addFlash('danger', "L'article que vous recherchez n'existe pas.");
            return $this->redirectToRoute(self::REDIRECT);
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $picture = $form->get('picture')->getData();
            if ($picture) {
                try {
                    if ($post->getPicture()) {
                        unlink($this->getParameter(self::UPLOAD) .'/' . $post->getPicture());
                    }
                    $pictureName = md5(uniqid()). '.' . $picture->guessExtension();
                    // Permet de déplacer l'image dans un dossier d'upload
                    // move() prend 2 paramètres: le dossier d'upload et le nom du fichier
                    $picture->move(
                        $this->getParameter(self::UPLOAD),
                        $pictureName
                    );
                    $post->setPicture($pictureName);
                } catch (FileException $e) {
                    $this->addFlash('danger', "Une erreur s'est produite lors de l'enregistrement de l'image avec le message suivant: ". $e->getMessage());
                }
            }

            $this->repository->save($post, true);
            $this->addFlash('success', "L'article a été modifié.");
            return $this->redirectToRoute(self::REDIRECT);
        }

        return $this->renderForm("post/update.html.twig", [
            'form' => $form
        ]);
    }

    #[Route('/{id}/delete', name:"delete", requirements:['id' => "\d+"])]
    public function delete(int $id): Response
    {
        $post = $this->repository->find($id);
        if ($post) {
            $this->repository->remove($post, true);
        } else {
            $this->addFlash('danger', "L'article que vous recherchez n'existe pas.");
        }
        return $this->redirectToRoute(self::REDIRECT);
    }
}

<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Service\UploadFile;
// use App\Entity\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    private const UPLOAD = "category_upload";

    public function __construct(
        private ManagerRegistry $manager
    ){}

    #[Route('/category', name: 'app_category')]
    // public function index(CategoryRepository $repository): Response
    public function index(): Response
    {
        // $categories = $repository->findAll(); // Symfony 4, on charge le Repository en paramètre et on l'utilise directement
        $categories = $this->manager->getRepository(Category::class)->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * A chaque appel d'une méthode dans le controller, 
     * les informations des attributs récupérés par la Request sont accessibles
     * On peut ainsi accéder à l'id reçu dans la route.
     * On peut récupérer directement la catégorie en paramètre de la méthode 
     * grâce à ces attributs mais un mauvais id retournera une erreur de paramConverter
     * Il peut être plus intéressant de gérer la récupération de l'Entité par nous-même
     * afin de mettre quelques vérifications.
     */
    #[Route('/category/{id}', name:'show_category', methods:['GET'], requirements:['id' => "\d+"])]
    public function show(int $id): Response
    // public function show(Category $category): Response
    {
        $category = $this->manager->getRepository(Category::class)->find($id);
        if (!$category) {
            $this->addFlash('danger', "La catégorie que vous recherchez n'existe pas.");
            return $this->redirectToRoute('app_category');
        }
        return $this->render('category/show.html.twig', [
            'category' => $category
        ]);
    }

    #[Route('/category/add', name:'add_category', methods:["GET", "POST"])]
    public function add(Request $request): Response
    {
        $category = new Category;
        // $this->createFormBuilder() permet de créer un générateur de formulaire
        // $form = $this->createFormBuilder($category)
        //    // On utilise la méthode add pour ajouter des champs à notre formulaire
        //     ->add('name', TextType::class, [
        //         'label' => "Nom de la catégorie",
        //         'attr' => [
        //             'placeholder' => "Nom de la catégorie"
        //         ],
        //         'row_attr' => [
        //             'class' => 'mb-3 form-floating'
        //         ]
        //     ])
        //     ->add('button', SubmitType::class, [
        //         'label' => "Ajouter"
        //     ])
        //     // La méthode getForm permet de récupérer le formulaire généré
        //     ->getForm()
        // ;
        $form = $this->createForm(CategoryType::class, $category);
        // handleRequest() récupère les informations reçues du formulaire
        // et stockée dans la Request pour les associer à l'entité Category
        // passée en data du createFormBuilder
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form->get('picture')->getData();
            if ($picture) {
                $resultSave = UploadFile::saveFile($picture, $this->getParameter(self::UPLOAD));
                is_string($resultSave) ?
                    $category->setPicture($resultSave) :
                    $this->addFlash($resultSave['type'], $resultSave['message']);
            }

            $om = $this->manager->getManager();
            $om->persist($category);
            $om->flush();
            $this->addFlash('success', "La catégorie ".$category->getName()." a été ajoutée");

            return $this->redirectToRoute('app_category');
        }

        // Lorsqu'on passe un formulaire à notre template,
        // On utilise la méthode renderForm plutôt que render
        // qui va appliquer la méthdoe createView() sur le form 
        // pour en générer l'affichage
        return $this->renderForm('category/add.html.twig', [
            // 'form' => $form->createView()
            'form' => $form
        ]);
    }

    #[Route('/category/{id}/update', name:'update_category', methods:["GET", "POST"], requirements:['id' => "\d+"])]
    public function update(int $id, Request $request): Response
    {
        $category = $this->manager->getRepository(Category::class)->find($id);
        if (!$category) {
            $this->addFlash('danger', "La catégorie que vous recherchez n'existe pas.");
            return $this->redirectToRoute('app_category');
        }
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form->get('picture')->getData();
            if ($picture) {
                if ($category->getPicture()) {
                    unlink($this->getParameter(self::UPLOAD) .'/'. $category->getPicture());
                }
                $resultSave = UploadFile::saveFile($picture, $this->getParameter(self::UPLOAD));
                is_string($resultSave) ?
                    $category->setPicture($resultSave) :
                    $this->addFlash($resultSave['type'], $resultSave['message']);
            }

            $om = $this->manager->getManager();
            $om->persist($category);
            $om->flush();
            $this->addFlash('success', "La catégorie a bien été modifiée.");
            return $this->redirectToRoute('show_category', ['id' => $category->getId()]);
        }

        return $this->renderForm('category/update.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/category/{id}/delete', name:'delete_category', methods:["GET"], requirements:['id' => "\d+"])]
    public function delete(int $id): Response
    {
        $category = $this->manager->getRepository(Category::class)->find($id);

        if ($category) {
            $om = $this->manager->getManager();
            $om->remove($category);
            $om->flush();
            $this->addFlash('success', "La catégorie a été supprimée.");
        } else {
            $this->addFlash('danger', "La catégorie que vous recherchez n'existe pas.");
        }

        return $this->redirectToRoute('app_category');
    }
}

<?php
namespace App\EventSubscriber;

use App\Controller\CategoryController;
use App\Controller\HomeController;
use App\Repository\CategoryRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CategoryRepository $repository,
        private Environment $twig
    ){}

    /**
     * Méthode static à implémenter pour définir à quel événement on souscrit
     *
     * @return void
     */
    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => "onControllerEvent"
        ];
    }

    /**
     * Notre méthode contenant le code à éxecuter à chaque appel d'un controller
     *
     * @param ControllerEvent $event
     * @return void
     */
    public function onControllerEvent(ControllerEvent $event)
    {
        // $restrictedControllers = [
        //     [HomeController::class, 'home'],
        //     [CategoryController::class, 'index']
        // ];
        
        // if ($event->getController()[0] instanceof HomeController || $event->getController()[0] instanceof CategoryController) {
        //     return;
        // }
        $categories = $this->repository->findAll();
        $this->twig->addGlobal('categories', $categories);
    }
}
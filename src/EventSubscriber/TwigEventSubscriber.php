<?php
namespace App\EventSubscriber;

use App\Controller\CategoryController;
use App\Controller\HomeController;
use App\Repository\CategoryRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ErrorController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{

    private array $restrictedControllers = [
        [HomeController::class, 'home'],
        [CategoryController::class, 'index']
    ];

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
        $controller = $event->getController();
        // dd($event->getController());
        if ($event->getController() instanceof ErrorController) {
            return;
        }
        $controller[0] = $controller[0]::class;

        if (in_array($controller, $this->restrictedControllers)) {
            return;
        }

        $categories = $this->repository->findAll();
        $this->twig->addGlobal('categories', $categories);
    }
}
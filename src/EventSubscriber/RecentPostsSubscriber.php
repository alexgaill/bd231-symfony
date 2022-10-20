<?php

namespace App\EventSubscriber;

use Twig\Environment;
use App\Repository\PostRepository;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RecentPostsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PostRepository $repository,
        private Environment $twig
    ){}

    public function onKernelController(ControllerEvent $event): void
    {
        $this->twig->addGlobal('recentPosts', $this->repository->findBy([], ['createdAt' => "DESC"], 5));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}

<?php

// src/EventListener/AccessDeniedListener.php
namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedListener
{
    #[AsEventListener(event: KernelEvents::EXCEPTION, priority: 2)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof AccessDeniedException) {
            return;
        }

        // \dd($exception);

        $errors = [
            "message" => $exception->getMessage(),
            "code" => $exception->getCode()
        ];

        $event->setResponse(new JsonResponse($errors, Response::HTTP_FORBIDDEN));
    }
}

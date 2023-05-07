<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionJsonListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        // Get the exception object from the event
        $exception = $event->getThrowable();

        // Create a JSON response with the exception message
        $response = new JsonResponse([
            'error' => $exception->getMessage(),
        ]);

        // If the exception is an instance of HttpExceptionInterface, set the response status code
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            // Otherwise, use a 500 Internal Server Error status code
            $response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Set the response for the event
        $event->setResponse($response);
    }
}

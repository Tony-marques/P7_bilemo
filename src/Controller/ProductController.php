<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;

#[Route(path: "/api", name: "api_")]
class ProductController extends AbstractFOSRestController
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    #[Rest\Get('/products', name: 'get_products')]
    public function getProducts(): Response
    {
        $products = $this->productRepository->findAll();

        $view = $this->view($products, Response::HTTP_OK);

        return $this->handleView($view);
    }
}

<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route(path: "/api", name: "api_")]
class ProductController extends AbstractFOSRestController
{
    public function __construct(private ProductRepository $productRepository, private CacheInterface $cache)
    {
    }

    #[Rest\Get('/products', name: 'get_products')]
    #[View()]
    public function getProducts(PaginatorInterface $paginator, Request $request)
    {
        $products = $this->productRepository->findAll();

        $page= $request->query->get("page", 1);

        $pagination = $paginator->paginate($products, $page, 2);

        $cache = $this->cache->get("products", function (ItemInterface $item) use ($pagination) {
            $item->expiresAfter(3600);

            return $pagination;
        });

        return $cache;
    }

    #[Rest\Get('/products/{id}', name: 'get_product', requirements: ["id" => "\d+"])]
    #[View()]
    public function getProduct(Product $product)
    {
        $cache = $this->cache->get("product . {$product->getId()}", function(ItemInterface $item) use ($product) {
            $item->expiresAfter(3600);

            return $product;
        });

        return $cache;
    }
}

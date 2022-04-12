<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;


use App\Repository\ProductRepository;

/**
 * @Route("/api", name="api_product")
 */
class ProductController extends AbstractController
{


    /**
     * @Rest\Get("/products", name="all_products")
     */
    public function listAction(Request $request, ProductRepository $repo): Response
    {
        $page = $request->query->get('page', 1);

        $products = $repo->findAll();

        $data = [];

        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'brand' => $product->getBrand(),
                'model' => $product->getModel(),
                'created_at' => $product->getCreatedAt(),
                'updated_at' => $product->getUpdatedAt(),
            ];
        }
        return $this->json($data);
    }

    /**
     * @Rest\Get("/products/{id}", name="product_show")
     */
    public function show(ProductRepository $repo, $id): Response
    {
        $product = $repo->findOneBy(['id' => $id]);

        if (!$product) {

            return $this->json('Aucun ne produit ne correspond à cet id' . $id, 404);
        }

        $data =  [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'brand' => $product->getBrand(),
            'model' => $product->getModel(),
            'created_at' => $product->getCreatedAt(),
            'updated_at' => $product->getUpdatedAt(),
        ];

        return $this->json($data);
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProductRepository;

/**
 * @Route("/api", name="api_product")
 */
class ProductController extends AbstractController
{


    /**
     * @Route("/products", name="all_products")
     */
    public function index(ProductRepository $repo): Response
    {
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
     * @Route("/products/{id}", name="product_show", methods={"GET"})
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

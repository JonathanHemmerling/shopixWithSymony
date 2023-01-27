<?php

declare(strict_types=1);
namespace App\Component\Product\Communication\Controller;

use App\Component\Product\Persistence\MainCategorysRepository;
use App\Component\Product\Persistence\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly MainCategorysRepository $mainCategorysRepository,
        private readonly ProductRepository $productRepository
    ) {
    }

    #[Route("/entry", name: 'entry')]
    public function entry(): Response
    {
        if($this->isGranted('ROLE_ADMIN')){
            return $this->render('admin/adminOverview.html.twig');
        }
        $mainMenu = $this->mainCategorysRepository->findAll();
        return $this->render('product/mainMenu.html.twig', ['menu' => $mainMenu]);
    }


    #[Route("/product/allProducts/{mainId}", name: "allProducts")]
    public function products($mainId): Response
    {
        $products = $this->productRepository->findBy(['mainId' => $mainId]);
        return $this->render('product/allProducts.html.twig', ['products' => $products]);
    }

    #[Route("/product/product/{productId}", name: "product")]
    public function product($productId): Response
    {
        $product = $this->productRepository->findBy(['id' => $productId]);
        return $this->render('product/product.html.twig', ['product' => $product]);
    }
}
<?php

declare(strict_types=1);
namespace App\Component\Product\Communication\Controller;

use App\Component\Product\Persistence\ProductRepository;
use App\Message\MyMessageDto;
use App\Message\ProductMessageDto;
use App\Repository\CategoryRepository;
use App\Repository\ProductsRepository;
use App\Service\CsvImport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categorysRepository,
        private readonly ProductsRepository $productsRepository,
    ) {
    }

    #[Route("/entry", name: 'entry')]
    public function entry(): Response
    {
        if($this->isGranted('ROLE_ADMIN')){
            return $this->render('admin/adminOverview.html.twig');
        }
        $mainMenu = $this->categorysRepository->findAll();
        return $this->render('product/mainMenu.html.twig', ['menu' => $mainMenu]);
    }


    #[Route("/product/allProducts/{mainId}", name: "allProducts")]
    public function products($mainId): Response
    {
        $products = $this->productsRepository->findBy(['category' => $mainId]);
        return $this->render('product/allProducts.html.twig', ['products' => $products]);
    }

    #[Route("/product/product/{productId}", name: "product")]
    public function product($productId): Response
    {
        $product = $this->productsRepository->findBy(['id' => $productId]);
        return $this->render('product/product.html.twig', ['product' => $product[0]]);
    }
}
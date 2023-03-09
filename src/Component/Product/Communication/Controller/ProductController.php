<?php

declare(strict_types=1);
namespace App\Component\Product\Communication\Controller;

use App\Component\Categorystorage\Business\Model\CategoryStorage;
use App\Component\Product\Business\ProductHandler;
use App\Component\Product\Persistence\ProductRepository;
use App\Component\Productstorage\Business\Model\ProductStorage;
use App\Component\Productstorage\Persistence\ProductstorageEntityManager;
use App\Component\Productstorage\Persistence\ProductstorageRepository;
use App\Component\Service\ElasticSearch\Model\Document;
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
        private readonly ProductStorage $productStorage,
        private readonly CategoryStorage $categoryStorage,
        private readonly Document $document,
    ) {
    }

    #[Route("/entry", name: 'entry')]
    public function entry(): Response
    {
        if($this->isGranted('ROLE_ADMIN')){
            return $this->render('admin/adminOverview.html.twig');
        }
        $mainMenu = $this->document->getAllCategorys();
        return $this->render('product/mainMenu.html.twig', ['menu' => $mainMenu]);
    }


    #[Route("/product/allProducts/{categoryName}", name: "allProducts")]
    public function products(string $categoryName): Response
    {
        $products = $this->document->getAllProducts($categoryName);
        return $this->render('product/allProducts.html.twig', ['products' => $products]);
    }

    #[Route("/product/product/{productId}", name: "product")]
    public function product($productId): Response
    {
        $product = $this->productsRepository->findBy(['id' => $productId]);
        $product = $this->productStorage->getProductFromRedis((int)$productId);
        return $this->render('product/product.html.twig', ['product' => $product[0]]);
    }
}
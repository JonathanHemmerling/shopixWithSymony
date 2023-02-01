<?php

declare(strict_types=1);

namespace App\Component\Product\Communication\Controller;

use App\Component\Product\Business\MainMenuBusinessFascade;
use App\Component\Product\Business\ProductBusinessFascade;
use App\Component\Product\Communication\Form\MainCategoryCreateForm;
use App\Component\Product\Communication\Form\ProductCreateForm;
use App\Component\Product\Communication\Form\ProductSaveForm;
use App\Component\Product\Persistence\MainCategorysRepository;
use App\Component\Product\Persistence\ProductRepository;
use App\DTO\MainMenuDataTransferObject;
use App\DTO\ProductDataTransferObject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class AdminProductController extends AbstractController
{
    public function __construct(
        private readonly MainCategorysRepository $mainCategorysRepository,
        private readonly ProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductBusinessFascade $productFascade,
        private readonly MainMenuBusinessFascade $mainMenuFascade
    ) {
    }
    #[Route("/admin/mainmenu", name: 'adminMainMenu')]
    public function entry(): Response
    {
        $mainMenu = $this->mainCategorysRepository->findAll();
        return $this->render('admin/mainMenu.html.twig', ['menu' => $mainMenu]);
    }
    #[Route("/admin/createcategory", name: 'adminCreateCategory')]
    public function createNewCategory(Request $request): Response
    {
        $mainDto = new MainMenuDataTransferObject();
        $newCategory = $this->createForm(MainCategoryCreateForm::class, $mainDto);
        $newCategory->handleRequest($request);
        if ($newCategory->isSubmitted() && $newCategory->isValid()) {
            $this->mainMenuFascade->create($mainDto);

            return $this->redirect('/admin/mainmenu/');
        }
        return $this->render(
            'admin/createNewCategory.html.twig',
            ['MainCategoryCreateForm' => $newCategory->createView()]
        );
    }
    #[Route("/admin/allProducts/{mainId}", name: "adminAllProducts")]
    public function products($mainId): Response
    {
        $products = $this->productRepository->findBy(['mainId' => $mainId]);
        return $this->render('admin/allProducts.html.twig', ['products' => $products, 'mainId' => $mainId]);
    }
    #[Route("/admin/createproduct/{mainId}", name: 'adminCreateProduct')]
    public function createNewProduct(Request $request, int $mainId): Response
    {
        $productDTO = new ProductDataTransferObject();
        $newProductForm = $this->createForm(ProductCreateForm::class, $productDTO);
        $newProductForm->handleRequest($request);
        if ($newProductForm->isSubmitted() && $newProductForm->isValid()) {
            $this->productFascade->create($productDTO);
            return $this->redirect('/admin/allProducts/' . $mainId);
        }
        return $this->render('admin/createNewProduct.html.twig', ['ProductCreateForm' => $newProductForm->createView()]
        );
    }
    #[Route("/admin/product/{productId}", name: "adminProduct")]
    public function saveChangedProductData(Request $request, $productId): Response
    {
        $product = $this->productRepository->findBy(['id' => $productId]);
        $productDTO = new ProductDataTransferObject();
        $saveProduct = $this->createForm(ProductSaveForm::class, $productDTO);
        $saveProduct->handleRequest($request);

        if ($saveProduct->isSubmitted() && $saveProduct->isValid()) {
            $this->productFascade->save($product[0], $productDTO);
            return $this->redirectToRoute('adminMainMenu');
        }
        return $this->render(
            'admin/product.html.twig',
            ['ProductSaveForm' => $saveProduct->createView(), 'productById' => $product]
        );
    }
    #[Route("/admin/product/delete/{productId}/{mainId}", name: "adminProductDelete")]
    public function deleteProduct($productId, $mainId): Response
    {
        $singleProduct = $this->productRepository->find($productId);
        $products = $this->productRepository->findBy(['mainId' => $mainId]);
        if (isset($singleProduct)) {
            $this->entityManager->remove($singleProduct);
            $this->entityManager->flush();
            return $this->redirectToRoute('adminMainMenu');
        }
        return $this->render('admin/allProducts.html.twig', ['products' => $products, 'mainId' => $mainId]);
    }
}
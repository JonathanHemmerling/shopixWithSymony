<?php

declare(strict_types=1);

namespace App\Component\Product\Communication\Controller;

use App\Component\Category\Business\CategoryBusinessFascade;
use App\Component\Category\Communication\Form\CategoryCreateForm;
use App\Component\Product\Business\ProductBusinessFascade;
use App\Component\Product\Communication\Form\ProductCreateForm;
use App\Component\Product\Communication\Form\ProductSaveForm;
use App\DTO\CategoryDataTransferObject;
use App\DTO\ProductsDataTransferObject;
use App\Repository\AttributesRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductsRepository;
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
        private readonly CategoryRepository $categorysRepository,
        private readonly ProductsRepository $productsRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductBusinessFascade $productFascade,
        private readonly CategoryBusinessFascade $mainMenuFascade,
        private readonly AttributesRepository $attributesRepository
    ) {
    }
    #[Route("/admin/mainmenu", name: 'adminMainMenu')]
    public function entry(): Response
    {
        $mainMenu = $this->categorysRepository->findAll();
        return $this->render('admin/mainMenu.html.twig', ['menu' => $mainMenu]);
    }
    #[Route("/admin/createcategory", name: 'adminCreateCategory')]
    public function createNewCategory(Request $request): Response
    {
        $mainDto = new CategoryDataTransferObject();
        $newCategory = $this->createForm(CategoryCreateForm::class, $mainDto);
        $newCategory->handleRequest($request);
        if ($newCategory->isSubmitted() && $newCategory->isValid()) {
            $this->mainMenuFascade->create($mainDto);

            return $this->redirect('/admin/mainmenu/');
        }
        return $this->render(
            'admin/createNewCategory.html.twig',
            ['CategoryCreateForm' => $newCategory->createView()]
        );
    }
    #[Route("/admin/allProducts/{mainId}", name: "adminAllProducts")]
    public function products($mainId): Response
    {
        $products = $this->productsRepository->findBy(['category' => $mainId]);
        return $this->render('admin/allProducts.html.twig', ['products' => $products, 'mainId' => $mainId]);
    }
    #[Route("/admin/createproduct/{mainId}", name: 'adminCreateProduct')]
    public function createNewProduct(Request $request, int $mainId): Response
    {
        $productDTO = new ProductsDataTransferObject();
        $attributeArray = $this->attributesRepository->findAll();
        $category = $this->categorysRepository->findOneBy(['id' => $mainId]);
        $productDTO->attributes = $attributeArray;
        $productDTO->category = $category->getName();
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
        $product = $this->productsRepository->findOneBy(['id' => $productId]);
        $productDTO = new ProductsDataTransferObject();
        //$productDTO->attributes = $product->getAttribute()->getValues();
        $productDTO->productName = $product->getProductName();
        $productDTO->category = $product->getCategory()->getName();
        $productDTO->articleNumber = $product->getArticleNumber();
        $productDTO->price = $product->getPrice();
        $productDTO->description = $product->getDescription();
        $saveProduct = $this->createForm(ProductSaveForm::class, $productDTO);
        $saveProduct->handleRequest($request);
        if ($saveProduct->isSubmitted() && $saveProduct->isValid()) {
            $this->productFascade->save($product, $productDTO);
            return $this->redirectToRoute('adminMainMenu');
        }
        return $this->render(
            'admin/product.html.twig',
            ['ProductSaveForm' => $saveProduct->createView(), 'productById' => $product]
        );
    }
    #[Route("/admin/product/delete/{productId}/{category}/{mainId}", name: "adminProductDelete")]
    public function deleteProduct($productId, $category ,$mainId): Response
    {
        $singleProduct = $this->productsRepository->find($productId);
        $products = $this->productsRepository->findBy(['category' => $category]);
        if (isset($singleProduct)) {
            $this->entityManager->remove($singleProduct);
            $this->entityManager->flush();
            return $this->redirectToRoute('adminMainMenu');
        }
        return $this->render('admin/allProducts.html.twig', ['products' => $products, 'mainId' => $mainId]);
    }
}
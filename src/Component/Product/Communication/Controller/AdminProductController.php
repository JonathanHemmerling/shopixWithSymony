<?php

declare(strict_types=1);

namespace App\Component\Product\Communication\Controller;

use App\Component\Product\Business\ProductBusinessFascade;
use App\Component\Product\Communication\Form\NewMainCategoryFormType;
use App\Component\Product\Communication\Form\ProductCreateForm;
use App\Component\Product\Communication\Form\SaveProductFormType;
use App\Component\Product\Persistence\MainCategorysRepository;
use App\Component\Product\Persistence\ProductEntityManager;
use App\Component\Product\Persistence\ProductRepository;
use App\DTO\ProductsDataTransferObject;
use App\Entity\MainCategorys;
use App\Entity\Product;
use App\Model\Mapper\ProductsMapper;
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
        private readonly EntityManagerInterface $entityManager
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
        //DTO
        $mainCategory = new MainCategorys();
        $newCategory = $this->createForm(NewMainCategoryFormType::class, $mainCategory);
        $newCategory->handleRequest($request);
        if ($newCategory->isSubmitted() && $newCategory->isValid()) {
            $this->entityManager->persist($mainCategory);
            $this->entityManager->flush();

            return $this->redirect('/admin/mainmenu/');
        }
        return $this->render('admin/createNewCategory.html.twig', ['newMainCategoryForm' => $newCategory->createView()]);
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

        $newProductForm = $this->createForm(ProductCreateForm::class, );
        $newProductForm->handleRequest($request);
        if ($newProductForm->isSubmitted() && $newProductForm->isValid()) {
            $fascade = new ProductBusinessFascade(new ProductEntityManager($this->entityManager));
            $fascade ->save();

            return $this->redirect('/admin/allProducts/' . $mainId);
        }
        return $this->render('admin/createNewProduct.html.twig', ['newProductForm' => $newProductForm->createView()]);
    }
    #[Route("/admin/product/{productId}", name: "adminProduct")]
    public function saveChangedProductData(Request $request, $productId): Response
    {
        $findProduct = $this->productRepository->findBy(['id' => $productId]);
        $product = new Product();
        $saveProduct = $this->createForm(SaveProductFormType::class, $product);
        $saveProduct->handleRequest($request);
        if ($saveProduct->isSubmitted() && $saveProduct->isValid()) {
            $editedProduct = $this->productRepository->find(["id" => $productId]);
            $editedProduct->setMainId($saveProduct->get('mainId')->getData());
            $editedProduct->setProductName($saveProduct->get('productName')->getData());
            $editedProduct->setDisplayName($saveProduct->get('displayName')->getData());
            $editedProduct->setDescription($saveProduct->get('description')->getData());
            $editedProduct->setPrice($saveProduct->get('price')->getData());
            $this->productRepository->save($editedProduct, true);

            return $this->redirectToRoute('adminMainMenu');
        }
        return $this->render('admin/product.html.twig', ['saveProduct' => $saveProduct->createView(), 'productById' => $findProduct]);
    }
    #[Route("/admin/product/delete/{productId}/{mainId}", name: "adminProductDelete")]
    public function deleteProduct($productId, $mainId): Response
    {
        $singleProduct = $this->productRepository->find($productId);
        $products = $this->productRepository->findBy(['mainId' => $mainId]);
        if(isset($singleProduct)) {
            $this->entityManager->remove($singleProduct);
            $this->entityManager->flush();

            return $this->redirectToRoute('adminMainMenu');
        }
        return $this->render('admin/allProducts.html.twig', ['products' => $products, 'mainId' => $mainId]);
    }
}
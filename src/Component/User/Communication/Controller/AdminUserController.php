<?php

declare(strict_types=1);

namespace App\Component\User\Communication\Controller;

use App\Component\User\Business\UserBusinessFascade;
use App\Component\User\Communication\Form\NewUserFormType;
use App\Component\User\Communication\Form\SaveUserFormType;
use App\Component\User\Persistence\Repository\UserRepository;
use App\DTO\UserDataTransferObject;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[AsController]
class AdminUserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager, private readonly UserBusinessFascade $userBusinessFascade
    ) {
    }
    #[Route("/admin/useroverview", name: 'adminUserOverview')]
    public function entry(): Response
    {
        $allUser = $this->userRepository->findAll();
        return $this->render('/admin/userOverview.html.twig', ['allUser' => $allUser]);
    }
    #[Route("/admin/createUser", name: 'adminCreateUser')]
    public function createNewUser(Request $request): Response
    {
        $newUserDto = new UserDataTransferObject();
        $newUserForm = $this->createForm(NewUserFormType::class, $newUserDto);
        $newUserForm->handleRequest($request);

        if ($newUserForm->isSubmitted() && $newUserForm->isValid()) {
            $this->userBusinessFascade->create($newUserDto);

           return $this->redirectToRoute('adminUserOverview');
        }
        return $this->render('admin/createNewUser.html.twig', [
            'newUserForm' => $newUserForm->createView(),
        ]);
    }
    #[Route("/admin/user/{userId}", name: "adminSaveUserData")]
    public function saveChangedUserData(Request $request, $userId): Response
    {
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        $userDto = new UserDataTransferObject();
        $userDto->email= $user->getEmail();
        $saveUser = $this->createForm(SaveUserFormType::class, $userDto );
        $saveUser->handleRequest($request);

        if ($saveUser->isSubmitted() && $saveUser->isValid()) {
            $this->userBusinessFascade->save($user, $userDto);
            return $this->redirectToRoute('adminMainMenu');
        }
        return $this->render('admin/user.html.twig', ['saveUser' => $saveUser->createView(), 'users' => $user]);
    }

    #[Route("/admin/user/delete/{userId}", name:"adminUserDelete")]
    public function deleteProduct($userId):Response
    {
        $singleUser = $this->userRepository->find($userId);
        $this->entityManager->remove($singleUser);
        $this->entityManager->flush();
        $allUser = $this->userRepository->findAll();
        return $this->render('admin/userOverview.html.twig', ['allUser' => $allUser]);
    }
}
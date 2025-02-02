<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\UserType;

class UserAdminController extends AbstractController
{
    #[Route('/admin/users/', name: 'app_user_admin')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        return $this->render('user_admin/index.html.twig', [
            'controller_name' => $users,
        ]);
    }

    #[Route('/admin/users/delete/{id}', name: 'app_user_delete')]
    public function confirmDelete(EntityManagerInterface $entityManager, User $user): Response
    {
        return $this->render('user_admin/delete.html.twig', [
            'user' => $user,
        ]);
    }


    #[Route('/admin/users/delete/confirmed/{id}', name: 'app_user_delete_confirmed')]
    public function delete(EntityManagerInterface $entityManager, User $user): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', 'User supprimé avec succès !');
        return $this->redirectToRoute('list_user');
    }

    #[Route('admin/users/list', name: 'list_user')]
    public function listUser(EntityManagerInterface $entityM)
    {
        $list = $entityM->getRepository(User::class)->findAll();

        return $this->render('user_admin/list.html.twig', ['liste_user' => $list,]);
    }


    #[Route('/admin/users/edit/{id}', name: 'app_user_edit')]
    public function edit(EntityManagerInterface $entityManager, Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'User mis à jour avec succès!');
            return $this->redirectToRoute('list_user');
        }

        return $this->render('user_admin/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }


    #[Route('admin/users/showUser/{id}', name: 'card_user')]
    public function showUser(EntityManagerInterface $entityM, string $id)
    {
        $user = $entityM->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                "NO FOUND USER"
            );
        }

        $this->addFlash('success', 'User loaded !');

        return $this->render('user_admin/showUser.html.twig', [
            'user' => $user,
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/utilisateurs')]
#[IsGranted('ROLE_ADMIN')]
class AdminUtilisateurController extends AbstractController
{
    #[Route('/', name: 'app_admin_utilisateur_index', methods: ['GET'])]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('admin_utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash du mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $utilisateur,
                $form->get('password')->getData()
            );
            $utilisateur->setPassword($hashedPassword);

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');

            return $this->redirectToRoute('app_admin_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_utilisateur/new.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_utilisateur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UtilisateurType::class, $utilisateur, ['is_new' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si un nouveau mot de passe est fourni
            if ($form->get('password')->getData()) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $utilisateur,
                    $form->get('password')->getData()
                );
                $utilisateur->setPassword($hashedPassword);
            }

            $utilisateur->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès.');

            return $this->redirectToRoute('app_admin_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/toggle-active', name: 'app_admin_utilisateur_toggle_active', methods: ['POST'])]
    public function toggleActive(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle-active'.$utilisateur->getId(), $request->request->get('_token'))) {
            $utilisateur->setIsActive(!$utilisateur->isActive());
            $utilisateur->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $status = $utilisateur->isActive() ? 'activé' : 'désactivé';
            $this->addFlash('success', "Utilisateur {$status} avec succès.");
        }

        return $this->redirectToRoute('app_admin_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_admin_utilisateur_delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            $entityManager->remove($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }
}
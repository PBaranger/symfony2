<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Cast\String_;
use Symfony\Bundle\MakerBundle\Doctrine\EntityDetails;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ArticleRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function index(ArticleRepository $articleRepository): Response
    {
        $dernier_articles = $articleRepository->findBy([], ['date' => 'DESC'], 3);

        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
            'liste_articles' => $dernier_articles,

        ]);
    }

    #[Route('/article/generate', name: 'generate_article')]
    public function generateArticlet(EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $str_now = date('Y-m-d H:i:s', time());
        $article->setTitre('Titre aleatoire #' . $str_now);
        $content = file_get_contents('http://loripsum.net/api');
        $article->setTexte($content);
        $article->setPublie(true);
        $article->setDate(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $str_now));
        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($article);
        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
        return new Response('Saved new article with id ' . $article->getId());
    }

    #[Route('article/list', name: 'list_article')]
    public function listArt(EntityManagerInterface $entityM)
    {
        $list = $entityM->getRepository(Article::class)->findAll();

        return $this->render('article/list.html.twig', ['liste_articles' => $list,]);
    }


    #[Route('article/showArt/{id}', name: 'card_article')]
    public function showArt(EntityManagerInterface $entityM, Request $request, string $id)
    {
        $article = $entityM->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException(
                "NO FOUND ARTICLE"
            );
        }

        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire->setArticle($article);
            $entityM->persist($commentaire);
            $entityM->flush();
            $this->addFlash('success', 'Commentaire ajouté avec succès !');
            return $this->redirectToRoute('card_article', ['id' => $id]);
        }

        $commentaires = $article->getCommentaires();

        $this->addFlash('success', 'Article loaded !');

        return $this->render('article/showArt.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
            'commentaires' => $commentaires,
        ]);
    }



    #[Route('/article/new', name: 'article_new')]
    public function new(EntityManagerInterface $entityManager, Request $request): Response
    {
        $article = new Article();
        $article->setTitre('Which Title ?');
        $article->setTexte('And which content ?');
        $now = time();
        $str_now = date('Y-m-d H:i:s', $now);
        $article->setDate(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $str_now));

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('list_article');
            $this->addFlash('success', 'Article ajouté avec succès !');
        }


        return $this->render('article/new.html.twig', ['form' => $form->createView(), 'article' => $article,]);
    }



    #[Route('/article/edit/{id}', name: 'edit_article')]
    public function edit(EntityManagerInterface $entityManager, Request $request, Article $article): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Article mis à jour avec succès!');
            return $this->redirectToRoute('list_article');
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }


    #[Route('/article/delete/{id}', name: 'delete_article')]
    public function confirmDelete(EntityManagerInterface $entityManager, Article $article): Response
    {
        return $this->render('article/delete.html.twig', [
            'article' => $article,
        ]);
    }


    #[Route('/article/delete/confirmed/{id}', name: 'delete_article_confirmed')]
    #[IsGranted('ROLE_ARTICLE_ADMIN', statusCode: 403, message: 'You are not allowed to access the Super admin dashboard.')]
    public function delete(EntityManagerInterface $entityManager, Article $article): Response
    {
        $entityManager->remove($article);
        $entityManager->flush();
        $this->addFlash('success', 'Article supprimé avec succès !');
        return $this->redirectToRoute('list_article');
    }
}

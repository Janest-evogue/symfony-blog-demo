<?php


namespace App\Controller\Admin;


use App\Entity\Article;
use App\Form\ArticleType;
use App\Form\SearchArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController
 * @package App\Controller\Admin
 *
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/page-{page}", requirements={"page": "\d+"})
     * @Route("/", name="app_admin_article_home")
     */
    public function index(Request $request, ArticleRepository $repository, $page = 1)
    {
//        $articles = $repository->findBy([], ['publicationDate' => 'DESC']);

        // formulaire de recherche
        $searchForm = $this->createForm(SearchArticleType::class);

        $searchForm->handleRequest($request);

        // données du formulaire
        //dump($searchForm->getData());

        // sans pagination :
        //$articles = $repository->search((array)$searchForm->getData());

        // avec pagination :
        $queryBuilder = $repository->getSearchQb((array)$searchForm->getData());
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pager = new Pagerfanta($adapter);
        $pager
            ->setCurrentPage($page)
            ->setMaxPerPage($this->getParameter('nb_articles_per_page'))
        ;

        return $this->render(
            'admin/article/index.html.twig',
            [
                // sans pagination :
                //'articles' => $articles,
                // avec pagination :
                'articles' => $pager->getCurrentPageResults(),
                'pager' => $pager,
                'search_form' => $searchForm->createView()
            ]
        );
    }

    /**
     * @Route("/edition/{id}", defaults={"id": null}, requirements={"id": "\d+"})
     */
    public function edit(Request $request, EntityManagerInterface $manager, $id)
    {
        // intégrer le formulaire pour l'enregistrement d'un article
        // Validation : tous les champs obligatoires
        // Avant l'enregistrement, setter la date de publication à maintenant
        // et l'auteur avec l'utilisateur connecté ($this->getUser() dans un contrôleur)
        // Bonus : faire marcher la page pour la modification

        $originalImage = null;

        if (is_null($id)) { // création
            $article = new Article();
            $article->setAuthor($this->getUser());
        } else { // modification
            $article = $manager->find(Article::class, $id);

            if (is_null($article)) {
                throw new NotFoundHttpException();
            }

            // si l'article contient une image
            if (!is_null($article->getImage())) {
                // nom du fichier venant de la bdd
                $originalImage = $article->getImage();

                // on sette l'image avec un objet File pour le champ de formulaire
                $article->setImage(
                    new File($this->getParameter('upload_dir') . $originalImage)
                );
            }
        }

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var UploadedFile|null $image */
                $image = $article->getImage();

                // s'il y a eu une image uploadée
                if (!is_null($image)) {
                    // nom sous lequel on va enregistrer l'image
                    $filename = uniqid() . '.' . $image->guessExtension();

                    // déplace l'image uploadée
                    $image->move(
                        // répertoire de destination
                        // cf config/services.yaml
                        $this->getParameter('upload_dir'),
                        // nom du fichier
                        $filename
                    );

                    // on sette l'image de l'article avec
                    // le nom du fichier pour enregistrement en bdd
                    $article->setImage($filename);

                    // en modification, on supprime l'ancienne image
                    // s'il y en a une
                    if (!is_null($originalImage)) {
                        unlink($this->getParameter('upload_dir') . $originalImage);
                    }
                } else {
                    // en modification, sans upload, on sette l'image
                    // avec le nom de l'ancienne image
                    $article->setImage($originalImage);
                }

                $manager->persist($article);
                $manager->flush();

                $this->addFlash('success', "L'article est enregistré");

                return $this->redirectToRoute('app_admin_article_index');
            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs');
            }
        }

        return $this->render(
            'admin/article/edit.html.twig',
            [
                'form' => $form->createView(),
                'original_image' => $originalImage
            ]
        );
    }

    /**
     * @Route("/suppression/{id}")
     */
    public function delete(EntityManagerInterface $manager, Article $article)
    {
        // si l'article a une image, on la supprime
        if (!is_null($article->getImage())) {
            $file = $this->getParameter('upload_dir') . $article->getImage();

            if (file_exists($file)) {
                unlink($file);
            }
        }

        $manager->remove($article);
        $manager->flush();

        $this->addFlash('success', "L'article est supprimé");

        return $this->redirectToRoute('app_admin_article_index');
    }

    /**
     * @Route("/ajax/contenu/{id}")
     */
    public function ajaxContent(Article $article)
    {
        // sans ckeditor :
        // return new Response(nl2br($article->getContent()));
        //avec :
        return new Response($article->getContent());
    }
}

<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleController extends FOSRestController
{
    /**
     * @FOSRest\Get("/api/articles")
     *
     * @param ObjectManager $manager
     *
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getArticlesAction(ObjectManager $manager, SerializerInterface $serializer)
    {
        $articleRepository = $manager->getRepository(Article::class);
        $articles = $articleRepository->findAll();

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($articles, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Get("/api/articles/{id}")
     *
     * @param ObjectManager $manager
     * @param SerializerInterface $serializer
     * @param $id
     *
     * @return Response
     */
    public function getArticleAction(ObjectManager $manager, SerializerInterface $serializer, $id)
    {
        $articleRepository = $manager->getRepository(Article::class);
        $article = $articleRepository->find($id);

        if (is_null($article)) {
            return new Response('Article not found', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
        }

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($article, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Post("/api/articles/new")
     *
     * @ParamConverter("article", converter="fos_rest.request_body")
     *
     * @param Article $article
     * @param ObjectManager $manager
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param Request $request
     *
     * @return Response
     */
    public function postArticleAction(Article $article, ObjectManager $manager, ValidatorInterface $validator, SerializerInterface $serializer, Request $request)
    {
        $newArticle = new Article();

        $articleForm = $this->createForm(ArticleType::class, $newArticle);
        $articleForm->submit($request->request->all());

        $errors = $validator->validate($article);

        if (!count($errors) ) {
            $jsonObject = $serializer->serialize($article, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            $manager->persist($newArticle);
            $manager->flush();
            return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response('Error', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Put("/api/articles/{id}")
     *
     * @ParamConverter("article", converter="fos_rest.request_body")
     *
     * @param Request $request
     * @param Article $article
     * @param ObjectManager $manager
     * @param $id
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    public function putArticleAction(Request $request, Article $article, ObjectManager $manager, $id, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $articleRepository = $manager->getRepository(Article::class);
        $savedArticle = $articleRepository->find($id);

        $articleForm = $this->createForm(ArticleType::class, $savedArticle);
        $articleForm->submit($request->request->all());

        $errors = $validator->validate($article);

        if (!count($errors) ) {
            $jsonObject = $serializer->serialize($article, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            $manager->persist($savedArticle);
            $manager->flush();
            return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response('Error', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Delete("/api/articles/{id}")
     *
     * @param ObjectManager $manager
     * @param $id
     *
     * @return Response
     */
    public function deleteArticleAction(ObjectManager $manager, $id)
    {
        $articleRepository = $manager->getRepository(Article::class);
        $article = $articleRepository->find($id);

        if (!is_null($article)) {
            $manager->remove($article);
            $manager->flush();
            return new Response('Ok', Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response('Error', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
    }
}

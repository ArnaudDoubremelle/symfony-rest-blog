<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthorController extends FOSRestController
{
    /**
     * @FOSRest\Get("/api/authors")
     *
     * @param ObjectManager $manager
     *
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getAuthorsAction(ObjectManager $manager, SerializerInterface $serializer)
    {
        $authorRepository = $manager->getRepository(Author::class);
        $authors = $authorRepository->findAll();

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($authors, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Post("/api/authors/new")
     *
     * @ParamConverter("author", converter="fos_rest.request_body")
     *
     * @param Author $author
     * @param ObjectManager $manager
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param Request $request
     *
     * @return Response
     */
    public function postAuthorAction(Author $author, ObjectManager $manager, ValidatorInterface $validator, SerializerInterface $serializer, Request $request)
    {
        $newAuthor = new Author();

        $authorForm = $this->createForm(AuthorType::class, $newAuthor);
        $authorForm->submit($request->request->all());

        $errors = $validator->validate($author);

        if (!count($errors) ) {
            $jsonObject = $serializer->serialize($author, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            $manager->persist($newAuthor);
            $manager->flush();
            return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response('Error', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Get("/api/authors/{id}")
     *
     * @param ObjectManager $manager
     * @param SerializerInterface $serializer
     * @param $id
     *
     * @return Response
     */
    public function getAuthorAction(ObjectManager $manager, SerializerInterface $serializer, $id)
    {
        $authorRepository = $manager->getRepository(Author::class);
        $author = $authorRepository->find($id);

        if (is_null($author)) {
            return new Response('Author not found', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
        }

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($author, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Put("/api/authors/{id}")
     *
     * @ParamConverter("author", converter="fos_rest.request_body")
     *
     * @param Request $request
     * @param Author $author
     * @param ObjectManager $manager
     * @param $id
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    public function putAuthorAction(Request $request, Author $author, ObjectManager $manager, $id, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $authorRepository = $manager->getRepository(Author::class);
        $savedAuthor = $authorRepository->find($id);

        $authorForm = $this->createForm(AuthorType::class, $savedAuthor);
        $authorForm->submit($request->request->all());

        $errors = $validator->validate($author);

        if (!count($errors) ) {
            $jsonObject = $serializer->serialize($author, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            $manager->persist($savedAuthor);
            $manager->flush();
            return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response('Error', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Delete("/api/authors/{id}")
     *
     * @param ObjectManager $manager
     * @param $id
     *
     * @return Response
     */
    public function deleteAuthorAction(ObjectManager $manager, $id)
    {
        $authorRepository = $manager->getRepository(Author::class);
        $author = $authorRepository->find($id);

        if (!is_null($author)) {
            $manager->remove($author);
            $manager->flush();
            return new Response('Ok', Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response('Error', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
    }
}

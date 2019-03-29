<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommentController extends FOSRestController
{
    /**
     * @FOSRest\Get("/api/comments")
     *
     * @param ObjectManager $manager
     *
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getCommentsAction(ObjectManager $manager, SerializerInterface $serializer)
    {
        $commentRepository = $manager->getRepository(Comment::class);
        $comments = $commentRepository->findAll();

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($comments, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Post("/api/comments/new")
     *
     * @ParamConverter("comment", converter="fos_rest.request_body")
     *
     * @param Comment $comment
     * @param ObjectManager $manager
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param Request $request
     *
     * @return Response
     */
    public function postCommentsAction(Comment $comment, ObjectManager $manager, ValidatorInterface $validator, SerializerInterface $serializer, Request $request)
    {
        $newComment = new Comment();

        $newComment = $this->createForm(CommentType::class, $newComment);
        $newComment->submit($request->request->all());

        $errors = $validator->validate($comment);

        if (!count($errors) ) {
            $jsonObject = $serializer->serialize($comment, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            $manager->persist($newComment);
            $manager->flush();
            return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response('Error', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Get("/api/comments/{id}")
     *
     * @param ObjectManager $manager
     * @param SerializerInterface $serializer
     * @param $id
     *
     * @return Response
     */
    public function getCommentAction(ObjectManager $manager, SerializerInterface $serializer, $id)
    {
        $commentRepository = $manager->getRepository(Comment::class);
        $comment = $commentRepository->find($id);

        if (is_null($comment)) {
            return new Response('Comment not found', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
        }

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($comment, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Put("/api/comments/{id}")
     *
     * @ParamConverter("comment", converter="fos_rest.request_body")
     *
     * @param Request $request
     * @param Comment $comment
     * @param ObjectManager $manager
     * @param $id
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    public function putCommentAction(Request $request, Comment $comment, ObjectManager $manager, $id, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $commentRepository = $manager->getRepository(Comment::class);
        $savedComment = $commentRepository->find($id);

        $commentForm = $this->createForm(CommentType::class, $savedComment);
        $commentForm->submit($request->request->all());

        $errors = $validator->validate($comment);

        if (!count($errors) ) {
            $jsonObject = $serializer->serialize($comment, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            $manager->persist($savedComment);
            $manager->flush();
            return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response('Error', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Delete("/api/comments/{id}")
     *
     * @param ObjectManager $manager
     * @param $id
     *
     * @return Response
     */
    public function deleteCommentAction(ObjectManager $manager, $id)
    {
        $commentRepository = $manager->getRepository(Comment::class);
        $comment = $commentRepository->find($id);

        if (!is_null($comment)) {
            $manager->remove($comment);
            $manager->flush();
            return new Response('Ok', Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response('Error', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
    }
}

<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryController extends FOSRestController
{
    /**
     * @FOSRest\Get("/api/categories")
     *
     * @param ObjectManager $manager
     *
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getCategoriesAction(ObjectManager $manager, SerializerInterface $serializer)
    {
        $categoryRepository = $manager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($categories, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Post("/api/categories/new")
     *
     * @ParamConverter("category", converter="fos_rest.request_body")
     *
     * @param Category $category
     * @param ObjectManager $manager
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param Request $request
     *
     * @return Response
     */
    public function postCategoryAction(Category $category, ObjectManager $manager, ValidatorInterface $validator, SerializerInterface $serializer, Request $request)
    {
        $newCategory = new Category();

        $newCategory = $this->createForm(CategoryType::class, $newCategory);
        $newCategory->submit($request->request->all());

        $errors = $validator->validate($category);

        if (!count($errors) ) {
            $jsonObject = $serializer->serialize($category, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            $manager->persist($newCategory);
            $manager->flush();
            return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response('Error', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Get("/api/categories/{id}")
     *
     * @param ObjectManager $manager
     * @param SerializerInterface $serializer
     * @param $id
     *
     * @return Response
     */
    public function getCategoryAction(ObjectManager $manager, SerializerInterface $serializer, $id)
    {
        $categoryRepository = $manager->getRepository(Category::class);
        $category = $categoryRepository->find($id);

        if (is_null($category)) {
            return new Response('Category not found', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
        }

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($category, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Put("/api/categories/{id}")
     *
     * @ParamConverter("category", converter="fos_rest.request_body")
     *
     * @param Request $request
     * @param Category $category
     * @param ObjectManager $manager
     * @param $id
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    public function putCategoryAction(Request $request, Category $category, ObjectManager $manager, $id, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $categoryRepository = $manager->getRepository(Category::class);
        $savedCategory = $categoryRepository->find($id);

        $categoryForm = $this->createForm(CategoryType::class, $savedCategory);
        $categoryForm->submit($request->request->all());

        $errors = $validator->validate($category);

        if (!count($errors) ) {
            $jsonObject = $serializer->serialize($category, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            $manager->persist($savedCategory);
            $manager->flush();
            return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response('Error', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Delete("/api/categories/{id}")
     *
     * @param ObjectManager $manager
     * @param $id
     *
     * @return Response
     */
    public function deleteCategoryAction(ObjectManager $manager, $id)
    {
        $categoryRepository = $manager->getRepository(Category::class);
        $category = $categoryRepository->find($id);

        if (!is_null($category)) {
            $manager->remove($category);
            $manager->flush();
            return new Response('Ok', Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response('Error', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
    }
}

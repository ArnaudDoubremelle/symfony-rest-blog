<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Article;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $categories = [];
        $authors = [];
        $articles = [];

        // Category fixtures
        for ($i = 0; $i < 10; $i++) {
            $category = new Category();
            $category->setName('Category ' . $i);

            $manager->persist($category);

            $categories[] = $category;
        }

        // Author fixtures
        for ($i = 0; $i < 10; $i++) {
            $author = new Author();
            $author->setName('Author ' . $i);

            $manager->persist($author);

            $authors[] = $author;
        }

        // Article fixtures
        for ($i = 0; $i < 100; $i++) {
            $article = new Article();
            $article->setTitle('Article ' . $i);
            $article->setText('Lorem ispum' . $i);
            $article->setCategory($categories[rand(0, 9)]);
            $article->setAuthor($authors[rand(0, 9)]);

            $manager->persist($article);

            $articles[] = $article;
        }

        // Comment fixtures
        for ($i = 0; $i < 50; $i++) {
            $comment = new Comment();
            $comment->setContent("Lorem ispum" . $i);
            $comment->setArticle($articles[rand(0, 99)]);

            $manager->persist($comment);
        }

        $manager->flush();
    }
}

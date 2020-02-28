<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     * @param PostRepository $repo
     * @return Response
     */
    public function index(PostRepository $repo)
    {
        return $this->render('post/index.html.twig', [
            'posts' => $repo->findAll(),
        ]);
    }

    /**
     * Permet de liker ou unliker un article
     * @Route("/post/{id}/like", name="post_like")
     * @param Post $post
     * @param ObjectManager $manager
     * @param PostLikeRepository $likeRepository
     * @return Response
     */
    public function like(Post $post, ObjectManager $manager, PostLikeRepository $likeRepository): Response
    {
        $user = $this->getUser();
//        si user pas connecté
        if (!$user) return $this->json([
            'code' => 403,
            'message' => "Unauthorized"
        ], 403);

//        si un user like un article
        if ($post->isLikedByUser($user)) {
            $like = $likeRepository->findOneBy([
                    'post' => $post,
                    'user' => $user
                ]);
            $manager->remove($like);
            $manager->flush();

            return $this->json([
                'code' => 200,
                'message' => 'like bien supprimé',
                'likes' => $likeRepository->count(['post' => $post])
            ], 200);
        }
        $like = new PostLike();
        $like->setPost($post);
        $like->setUser($user);

        $manager->persist($like);
        $manager->flush();

        return $this->json([
            'code' => 200,
            'message' => 'like bien ajouté',
            'likes' => $likeRepository->count(['post' => $post])
        ], 200);
    }
}

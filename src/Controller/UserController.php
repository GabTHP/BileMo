<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;

/**
 * @Route("/api", name="api_user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/customers/{customer_id}/users", name="all_consumer_users")
     */
    public function index(UserRepository $repo, $customer_id): Response
    {
        $users = $repo->findBy(['customer' => $customer_id]);
 
        $data = [];
 
        foreach ($users as $user) {
           $data[] = [
               'id' => $user->getId(),
               'username' => $user->getUsername(),
               'firstName' => $user->getFirstName(),
               'lastName' => $user->getLastName(),
               'password' => $user->getPassword(),
               'created_at' => $user->getCreatedAt(),
           ];
        }
        return $this->json($data);
    }

        /**
     * @Route("/customers/{customer_id}/users/{user_id}", name="single_consumer_users")
     */
    public function show(UserRepository $repo, $customer_id, $user_id): Response
    {
        $user = $repo->findOneBy(['id' => $user_id]);

        if (!$product) {
 
            return $this->json('Aucun utilisateur ne correspond à cet id' . $id, 404);
        }
 
           $data[] = [
               'id' => $user->getId(),
               'username' => $user->getUsername(),
               'firstName' => $user->getFirstName(),
               'lastName' => $user->getLastName(),
               'password' => $user->getPassword(),
               'created_at' => $user->getCreatedAt(),
           ];

        return $this->json($data);
    }
}

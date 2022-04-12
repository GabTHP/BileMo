<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\CustomerRepository;
use App\Entity\User;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/api", name="api_user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/customers/{customer_id}/users", name="all_consumer_users", methods={"GET"})
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
     * @Route("/customers/{customer_id}/users/{user_id}", name="single_consumer_users", methods={"GET"})
     */
    public function show(UserRepository $repo, $customer_id, $user_id): Response
    {
        $user = $repo->findOneBy(['id' => $user_id]);

        if (!$user) {

            return $this->json('Aucun utilisateur ne correspond à cet id' . $user_id, 404);
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

    /**
     * @Route("/customers/{customer_id}/users", name="user_new", methods={"POST"})
     */
    public function new(Request $request, EntityManagerInterface $em, CustomerRepository $repo, $customer_id): Response
    {
        $user = new User();
        $customer = $repo->findOneBy(['id' => $customer_id]);
        $user->setUsername($request->request->get('username'));
        $user->setFirstName($request->request->get('first_name'));
        $user->setLastName($request->request->get('last_name'));
        $user->setEmail($request->request->get('email'));
        $user->setPassword($request->request->get('password'));
        $user->setCustomer($customer);

        $em->persist($user);
        $em->flush();

        return $this->json('Nouvel utilisateur créé avec succés, son id est le : ' . $user->getId());
    }

    /**
     * @Route("/customers/{customer_id}/users/{user_id}", name="delete_consumer_user", methods={"DELETE"})
     */
    public function delete(UserRepository $repo, EntityManagerInterface $em, $customer_id, $user_id): Response
    {
        $user = $repo->findOneBy(['id' => $user_id]);

        if (!$user) {

            return $this->json('Aucun utilisateur ne correspond à cet id' . $user_id, 404);
        }

        $em->remove($user);
        $em->flush();

        return $this->json('Utilisateur supprimé');
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\CustomerRepository;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/api", name="api_user")
 */

class UserController extends AbstractController
{
    /**
     * @Route("/customers/{customer_id}/users", name="all_consumer_users", methods={"GET"})
     */
    public function index(UserRepository $repo, CustomerRepository $repo_customer, $customer_id, PaginatorInterface $paginator, Request $request, TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager): Response
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $email = $decodedJwtToken['email'];
        $customer = $repo_customer->findOneBy(['email' => $email]);

        if ($customer->getId() != $customer_id)

            return $this->json('fuck you');

        $users = $repo->findBy(['customer' => $customer_id]);

        $users = $paginator->paginate(
            $users, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

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
    public function show(UserRepository $repo, CustomerRepository $repo_customer, $customer_id, $user_id, TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager): Response
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $email = $decodedJwtToken['email'];
        $customer = $repo_customer->findOneBy(['email' => $email]);

        if ($customer->getId() != $customer_id)

            return $this->json('fuck you');

        $user = $repo->findOneBy(['id' => $user_id]);

        if (!$user) {

            return $this->json('Aucun utilisateur ne correspond à cet id' . $user_id, 404);
        }

        if ($user->getCustomer()->getId() != $customer->getId())
            return $this->json('fuck you');

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
    public function delete(UserRepository $repo, CustomerRepository $repo_customer, EntityManagerInterface $em, $customer_id, $user_id, TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager): Response
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $email = $decodedJwtToken['email'];
        $customer = $repo_customer->findOneBy(['email' => $email]);

        if ($customer->getId() != $customer_id)

            return $this->json('fuck you');


        $user_to_delete = $repo->findOneBy(['id' => $user_id]);

        if ($user_to_delete->getCustomer()->getId() != $customer->getId())
            return $this->json('fuck you');

        if (!$user_to_delete) {

            return $this->json('Aucun utilisateur ne correspond à cet id' . $user_id, 404);
        }

        $em->remove($user_to_delete);
        $em->flush();

        return $this->json('Utilisateur supprimé');
    }
}

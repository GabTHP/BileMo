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
use Symfony\Component\Validator\Validator\ValidatorInterface;


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

            return $this->json("accès non autorisé", 401);

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
        return $this->json($data, 200);
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

            return $this->json("accès non autorisé", 401);

        $user = $repo->findOneBy(['id' => $user_id]);

        if (!$user) {

            return $this->json('Aucun utilisateur ne correspond à cet id ' . $user_id, 404);
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

        return $this->json($data, 200);
    }

    /**
     * @Route("/customers/{customer_id}/users", name="user_new", methods={"POST"})
     */
    public function new(ValidatorInterface $validator, Request $request, EntityManagerInterface $em, UserRepository $repo_user, CustomerRepository $repo, $customer_id): Response
    {

        $email = $request->request->get('email');
        $customer = $repo->findOneBy(['id' => $customer_id]);

        $user_check = $repo_user->findOneby(['email' => $email, 'customer' => $customer_id]);

        if ($user_check)

            return $this->json("Email déjà utilisé", 400);


        $user = new User();


        $user->setUsername($request->request->get('username'));
        $user->setFirstName($request->request->get('first_name'));
        $user->setLastName($request->request->get('last_name'));
        $user->setEmail($email);
        $user->setPassword($request->request->get('password'));
        $user->setCustomer($customer);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            /*
            * Uses a __toString method on the $errors variable which is a
            * ConstraintViolationList object. This gives us a nice string
            * for debugging.
            */
            $errorsString = (string) $errors;

            return $this->json($errorsString, 400);
        }
        $em->persist($user);
        $em->flush();

        $data[] = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'password' => $user->getPassword(),
            'created_at' => $user->getCreatedAt(),
        ];

        return $this->json($data, 201);
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

            return $this->json("accès non autorisé", 401);


        $user_to_delete = $repo->findOneBy(['id' => $user_id]);

        if ($user_to_delete->getCustomer()->getId() != $customer->getId())
            return $this->json("accès non autorisé", 401);

        if (!$user_to_delete) {

            return $this->json('Aucun utilisateur ne correspond à cet id' . $user_id, 404);
        }

        $em->remove($user_to_delete);
        $em->flush();

        return $this->json('Utilisateur supprimé', 200);
    }
}

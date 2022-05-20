<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Repository\UserRepository;
use App\Repository\CustomerRepository;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;
use App\Entity\Product;
use App\Entity\customer;
use Nelmio\ApiDocBundle\Annotation\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\Post;





/**
 * @Route("/api", name="api_user")
 */

class UserController extends AbstractController
{
    /**
     * @Rest\Get("/customers/{customer_id}/users", name="all_consumer_users")
     * 
     * @OA\Response(
     *      response=200,
     *      description="Return user list from a customer",
     *      @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class))
     *     )
     * )
     * 
     * @OA\Response(
     *     response = 401,
     *     description = "You must provide a valid token or you're not accessing the right customer account"
     * )
     * 
     * 
     * @Security(name="Bearer")
     * 
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="The field to show product from page, each page contain 10 products",
     *     @OA\Schema(type="string")
     * )
     * 
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
        return $this->json($data, 200)->setSharedMaxAge(3600);
    }

    /**
     * @Rest\GET("/customers/{customer_id}/users/{user_id}", name="single_consumer_users")
     * 
     * 
     * @OA\Response(
     *      response=200,
     *      description="Return single user details",
     *      @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class))
     *     )
     * )
     * 
     * @OA\Response(
     *     response = 401,
     *     description = "You must provide a valid token or you're not accessing the right customer account"
     * )
     * @Security(name="Bearer")
     * 
     * 
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
            return $this->json('accès non autoridé', 401);

        $data[] = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'password' => $user->getPassword(),
            'created_at' => $user->getCreatedAt(),
        ];

        return $this->json($data, 200)->setSharedMaxAge(3600);
    }

    /**
     * @Rest\Post("/customers/{customer_id}/users", name="user_new")
     * 
     * 
     * @OA\Response(
     *      response=201,
     *      description="Create new  user linked to a customer",
     *      @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class))
     *     )
     * )
     * 
     * @OA\Parameter(
     *    name="customer_id",
     *    in="path",
     *    description="ID of customer that needs to be used",
     *    required=true,
     *    @OA\Schema(
     *        type="integer",
     *        format="int64"
     *    )
     *  )
     * 
     * 
     *@OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/x-www-form-urlencoded",
     *       @OA\Schema(
     *         @OA\Property(property="username", description="The username of the new user.", example="oss117"),
     *         @OA\Property(property="first_name", type="string", example="Jean"),
     *         @OA\Property(property="last_name", description="The lastname of the new user.", type="string", example="Dujardin"),
     *         @OA\Property(property="email", description="Email address of the new user.", type="string", format="email", example="j.dujardin@gmail.com"),
     *         @OA\Property(property="password", description="Password of the new user.", type="string", format="password", example="Azerty34!!")
     *       )
     *     )
     *   )
     * 
     * @OA\Response(
     *     response = 400,
     *     description = "Les données saisies sont incorrectes."
     * )
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
     * 
     * 
     * @Rest\Delete("/customers/{customer_id}/users/{user_id}", name="delete_consumer_user")
     * 
     * @OA\Response(
     *      response=200,
     *      description="Delete User",
     *     )
     * )
     * @OA\Response(
     *     response = 401,
     *     description = "You must provide a valid token or you're not accessing the right customer account"
     * )
     * @Security(name="Bearer")
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

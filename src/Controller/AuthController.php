<?php

namespace App\Controller;

use \Firebase\JWT\JWT;
use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/api", name="api_auth")
 */
class AuthController extends AbstractController
{
    /**
     * @Route("/auth/login", name="login", methods={"POST"})
     */
    public function login(Request $request, CustomerRepository $repo, UserPasswordEncoderInterface $encoder)
    {
        $customer = $repo->findOneBy([
            'email' => $request->get('email'),
        ]);
        if (!$customer || !$encoder->isPasswordValid($customer, $request->get('password'))) {
            return $this->json([
                'message' => 'email or password is wrong.',
            ]);
        }
        $payload = [
            "user" => $customer->getEmail(),
            "exp"  => (new \DateTime())->modify("+5 minutes")->getTimestamp(),
        ];

        $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'), 'HS256');
        return $this->json([
            'message' => 'success!',
            'token' => sprintf('Bearer %s', $jwt),
        ]);
    }
}

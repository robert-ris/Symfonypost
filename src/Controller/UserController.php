<?php

namespace App\Controller;

use App\Entity\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    /**
     * @Route("/")
     * @param Request $request
     * @return Response
     */
    public function signUp(Request $request)
    {
        if (
            !$request->request->has('username') ||
            !$request->request->has('email') ||
            !$request->request->has('pass')
        ) {
            return $this->render('user/signup.html.twig');
        }

        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $password = $request->request->get('pass');

        $user = new User($username, $email, $password);

        if (empty($username) || empty($email)) {
            return new Response('All fields are required');
        } else {
            if (!preg_match("/^[a-zA-Z]*$/", $username)) {
                return new Response('Incorect data');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return new Response('Incorect e-mail adress');
            } else {
                $em = $this->getDoctrine()->getManager();

                $em->persist($user);
                $em->flush();

                return $this->render('user/login.html.twig');
            }
        }
    }

    /**
     * @Route("/login")
     * @param Request $request
     * @return Response
     */

    public function login(Request $request)
    {
        if (
            !$request->request->has('email') ||
            !$request->request->has('pass')
        ) {
            return $this->render(
                'user/login.html.twig',
                ['error' => '']
            );
        }

        $email = $request->request->get('email');
        $password = $request->request->get('pass');

        $repo = $this->getDoctrine()->getRepository(User::class);

        $user = $repo->findOneBy(['email' => $email]);

        if (is_null($user)) {
            return $this->render(
                'user/login.html.twig',
                ['error' => 'Invalid user']
            );
        }

        if (!$user->getPassword($password)) {
            return $this->render(
                'user/login.html.twig',
                ['error' => 'Invalid password']
            );
        }

        $request->getSession()->set('email', $email);
        $request->getSession()->set('userId', $user->getId());

        return $this->redirect('/index');
    }

    /**
     * @Route("/logout")
     * @param Request $request
     * @return Response
     */
    public function logout(Request $request)
    {
        $request->getSession()->invalidate();

        return $this->redirect('/login');
    }
}

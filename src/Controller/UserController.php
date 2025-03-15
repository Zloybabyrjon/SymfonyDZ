<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request, EntityManagerInterface $em): Response
    {
        $qb = $userRepository->createQueryBuilder('u');
        $qb->andWhere('u.last_name LIKE :search OR u.email LIKE :search')
            ->setParameter('search', ''.$request->query->get('search').'%');
        $users = $qb->getQuery()->getResult();
        return $this->render('/user/indexUser.html.twig', ['users' => $users]);
    }

    #[Route('/user', name: "create_user", methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $user = new User();
        $user->setFirstName($request->request->get('first_name'));
        $user->setLastName($request->request->get('last_name'));
        $user->setAge($request->request->get('age'));
        $user->setStatus($request->request->get('status'));
        $user->setEmail($request->request->get('email'));
        $user->setTelegram($request->request->get('telegram'));
        $user->setAddress($request->request->get('address'));
        $em->persist($user);
        $em->flush();

        return $this->redirect('/');
    }

    #[Route('/user/create')]
    public function formCreate(): Response
    {
        return $this->render('user/createUser.html.twig');
    }
    #[Route('/user/{user}', name: 'delete_user', methods: ["DELETE"])]
    public function delete(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();
        return $this->redirect('/user');
    }
    #[Route('/user/{user}', name: 'edit_user', methods: ["GET"])]
    public function edit(User $user)
    {
        return $this->render('user/editUser.html.twig', ['user' => $user]);
    }
    #[Route('/user/{user}', name: 'update_user', methods: ["PUT"])]
    public function update(User $user, Request $request, EntityManagerInterface $em)
    {
        $user->setFirstName($request->request->get('first_name'));
        $user->setLastName($request->request->get('last_name'));
        $user->setAge($request->request->get('age'));
        $user->setStatus($request->request->get('status'));
        $user->setEmail($request->request->get('email'));
        $user->setTelegram($request->request->get('telegram'));
        $user->setAddress($request->request->get('address'));

        $em->flush();

        return $this->redirect('/user');
    }
}

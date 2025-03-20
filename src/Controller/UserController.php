<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DepartmentRepository;
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
    public function index(DepartmentRepository $departmentRepository, UserRepository $userRepository,  Request $request, EntityManagerInterface $em): Response
    {
        
        $userQb = $userRepository->createQueryBuilder('user');

        $departmentValue = $request->query->get('department');

        $userQb->setParameter('search', ''.$request->query->get('search').'%');

        if($departmentValue != 0){
            $userQb->where('user.last_name LIKE :search  AND user.department = :search2 OR user.email LIKE :search AND user.department = :search2')->setParameter('search2', ''.$departmentValue);
        }
        else{
            $userQb->where('user.last_name LIKE :search OR user.email LIKE :search');
        } 
        $users = $userQb->getQuery()->getResult();

        
        $departmentQb = $departmentRepository->createQueryBuilder('d');
        $department = $departmentQb->getQuery()->getResult();

        
        return $this->render('/user/index.html.twig', ['users' => $users, 'department'=> $department]);
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
    public function formCreate(DepartmentRepository $departmentRepository, Request $request): Response
    {
        $qb = $departmentRepository->createQueryBuilder('u');
        $department = $qb->getQuery()->getResult();
        return $this->render('user/createUser.html.twig', ['department' => $department]);
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

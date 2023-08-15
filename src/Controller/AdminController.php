<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{

    #[Route('/user', name: 'user.list'), IsGranted('ROLE_ADMIN')]
    public function user(ManagerRegistry $doctrine): Response
    {
        $repository=$doctrine->getRepository(User::class);
        $users=$repository->findAll();
        $all=$repository->count([]);
        return $this->render('admin/user-list.html.twig', [
            'users' => $users,
            'all' => $all,
        ]);
    }

    #[Route('/user/details/{id}', name: 'user.details')]
    public function userDetails(ManagerRegistry $doctrine, $id): Response
    {
        $repository=$doctrine->getRepository(User::class);
        $user=$repository->find($id);
        if(!$user){
            $this->addFlash('danger'," User does not exist ! ");
            return $this->redirectToRoute('user.list');
        }
        return $this->render('admin/user-details.html.twig',['user'=>$user]);
    }

    #[Route('/user/edit/{id}', name: 'user.edit',methods:['GET','POST']),IsGranted('ROLE_ADMIN')]
    public function userEdit(User $user ,Request $request, ManagerRegistry $doctrine,UserPasswordEncoderInterface $passwordEncoder ,$id): Response
    {
        $repository=$doctrine->getRepository(User::class);
        $user=$repository->find($id);
        $form = $this->createForm(UserType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if ($passwordEncoder->isPasswordValid($user, $form->get('plainPassword')->getData())){
                $user=$form->getData();
                $manager=$doctrine->getManager();
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success',' User has been updated successfully !');
                return $this->redirectToRoute('user.list');
            }
            else{
                $this->addFlash('danger',' Wrong Password !');
            }
        }
        return $this->render('admin/user-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/edit/info/{id}', name: 'user.editinfo',methods:['GET','POST'])]
    public function userEditInfo(User $user ,UserPasswordEncoderInterface $passwordEncoder ,Request $request, ManagerRegistry $doctrine, $id): Response
    {
        $repository=$doctrine->getRepository(User::class);
        $user=$repository->find($id);
        $form = $this->createForm(UserType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if ($passwordEncoder->isPasswordValid($user, $form->get('plainPassword')->getData())){
                $user=$form->getData();
                $manager=$doctrine->getManager();
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success',' You have updated your account successfully !');
                return $this->redirectToRoute('event.list');
            }
            else{
                $this->addFlash('danger',' Wrong Password !');
            }
    
        }
        return $this->render('admin/user-editinfo.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    #[Route('/user/delete/{id}', name: 'user.delete'),IsGranted('ROLE_ADMIN')]
    public function userDelete(ManagerRegistry $doctrine, $id): RedirectResponse
    {   
        $repository=$doctrine->getRepository(User::class);
        $user=$repository->find($id);
        $this->container->get('security.token_storage')->setToken(null);
        if($user){
            $manager=$doctrine->getManager();
            $manager->remove($user);
            $manager->flush();
            $this->addFlash('danger',"User has been deleted !");
            return $this->redirectToRoute('user.list');
        }else{
            $this->addFlash('error',"User does not exist !");
        }
        return $this->redirectToRoute('user.list');
    }

    #[Route('/stats', name: 'stats'),IsGranted('ROLE_ADMIN') ]
    public function stats(ManagerRegistry $doctrine): Response
    {
        $repository=$doctrine->getRepository(User::class);
        $repository2=$doctrine->getRepository(Event::class);
        $allUsers=$repository->count([]);
        $allEvents=$repository2->count([]);
        return $this->render('admin/stats.html.twig', [
            'allUsers' => $allUsers,
            'allEvents'=>$allEvents
        ]);
    }
}


<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class EventController extends AbstractController
{
    #[Route('/event', name: 'event.list')]
    public function events(ManagerRegistry $doctrine): Response
    {
        $repository=$doctrine->getRepository(Event::class);
        $events=$repository->findAll();
        $nbEvents=$repository->count([]);
        return $this->render('admin/event-list.html.twig', [
            'events' => $events,
            'nbEvents'=>$nbEvents
        ]);
    }

    #[Route('/event/details/{id}', name: 'event.details')]
    public function eventDetails(ManagerRegistry $doctrine, $id): Response
    {
        $repository=$doctrine->getRepository(Event::class);
        $event=$repository->find($id);
        if(!$event){
            $this->addFlash('danger'," Event does not exist ! ");
            return $this->redirectToRoute('event.list');
        }
        return $this->render('admin/event-details.html.twig',['event'=>$event]);
    }

    #[Route('/event/edit/{id?0}', name: 'event.edit')]
    public function editEvent(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger ,$id): Response
    {
        $repository=$doctrine->getRepository(Event::class);
        $event=$repository->find($id);
        $new=false;
        if(!$event){
            $new=true;
            $event=new Event;
        }
        $form = $this->createForm(EventType::class,$event);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $image = $form->get('image')->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();
                // Move the file to the directory where brochures are stored
                try {
                    $image->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $event->setImage($newFilename);
            }
            $manager=$doctrine->getManager();
            $manager->persist($event);
            $manager->flush();
            if($new){
                $message=" is added successfully !";
            }
            else{
                $message=" is updated sucessfully !";
            }
            $this->addFlash('success',$event->getName(). '' . $message);
            return $this->redirectToRoute('event.list');
        }
        else{
            return $this->render('event/add-event.html.twig', [
                'form'=>$form->createView()
            ]);
        }
    }

    #[Route('/event/delete/{id}', name: 'event.delete')]
    public function deleteEvent(ManagerRegistry $doctrine, $id): Response
    {   
        $repository=$doctrine->getRepository(Event::class);
        $event=$repository->find($id);
        if($event){
            $manager=$doctrine->getManager();
            $manager->remove($event);
            $manager->flush();
            $this->addFlash('danger','Event has been Deleted Successfully !');
            return $this->redirectToRoute('event.list');
        }
        else{
            $this->addFlash('error',"Event does not exist !");
        }
    }

    #[Route('/event/join/{id}', name: 'event.join')]
    public function joinEvent(ManagerRegistry $doctrine, $id): Response
    {   
        $repository=$doctrine->getRepository(Event::class);
        $event=$repository->find($id);
        if($event){
            $manager=$doctrine->getManager();
            $event->addParticipant($this->getUser());
            $manager->persist($event);
            $manager->flush();
            $this->addFlash('success','You joined the event !');
            return $this->redirectToRoute('event.list');
        }
    }

    #[Route('/event/joined', name: 'event.joined')]
    public function joinedEvent(ManagerRegistry $doctrine): Response
    {   
        $events=$this->getUser()->getEvents();
        return $this->render('admin/events-joined.html.twig', [
            'events' => $events,
        ]);
    }

}

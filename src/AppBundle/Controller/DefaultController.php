<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Notes;
use AppBundle\Entity\User;
use AppBundle\Form\NotesType;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    protected function getU()
    {
       return $this->get('security.token_storage')->getToken()->getUser();
    }
   
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        if($this->getU() !== "anon.") {
            $note = $this->getDoctrine()
                ->getRepository('AppBundle:User')
                ->find($this->getU()->getId());

            if (!$note) {
                throw $this->createNotFoundException(
                    'No note found for id '
                );
            }
            $notes = $note->getNotes();

            $results = array();

            foreach($notes as $note) {
                $results[] = array(
                        'name' => $note->getName(),
                        'content' => $note->getContent(),
                        'id' => $note->getId()
                    );
            }

            return $this->render('default/index.html.twig', [
                'notes' => $results
            ]);  

        }
        return $this->render('default/index.html.twig');       
    }
    
    /**
     * @Route("/create/", name="create")
     */
    public function createAction(Request $request)
    {
        $user = $this->getU();
        $note = new Notes();


        $form = $this->createForm(NotesType::class, $note);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note = $form->getData();
            $note->setUser($user);

            $em = $this->getDoctrine()->getManager();

            // tells Doctrine you want to (eventually) save the note (no queries yet)
            $em->persist($note);
            //$em->persist($user);
            //die(dump());
            // actually executes the queries (i.e. the INSERT query)
            $em->flush();

            // unset($note);
            // unset($form);
            // $note = new Notes();
            // $form = $this->createForm(NotesType::class, $note);

            $this->addFlash(
                "notice",
                "{$note->getName()} Note Added"
            );

            return $this->redirectToRoute('homepage');
            
        }


        return $this->render('default/new.html.twig', array(
            'form' => $form->createView(),
        ));        




        // return new Response('Saved new note with id '.$note->getId());
    }


    /**
     * @Route("/update/{id}", name="update")
     */
    public function updateAction(Request $request, $id)
    {
        $user = $this->getU();
        $note = $this->getDoctrine()
            ->getRepository('AppBundle:Notes')
            ->find($id);


        $form = $this->createForm(NotesType::class, $note);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note = $form->getData();
            $note->setUser($user);

            $em = $this->getDoctrine()->getManager();

            // tells Doctrine you want to (eventually) save the note (no queries yet)
            $em->persist($note);
            //$em->persist($user);

            // actually executes the queries (i.e. the INSERT query)
            $em->flush();  

            $this->addFlash(
                'notice',
                'Note Updated'
            );
             
            return $this->redirectToRoute('homepage');

        }

        return $this->render('default/new.html.twig', array(
            'form' => $form->createView(),
        ));        

    }    
 



    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function deleteAction(Request $request, $id)
    {
        $user = $this->getU();
        $note = $this->getDoctrine()
            ->getRepository('AppBundle:Notes')
            ->find($id);
        
        $noteName = $note->getName();

        $em = $this->getDoctrine()->getManager();
        $em->remove($note);
        $em->flush();
        
        $this->addFlash(
            "notice",
            "$noteName Note Deleted"
        );

        return $this->redirectToRoute('homepage');   

    }    
 
}
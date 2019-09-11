<?php

namespace App\Controller\Eleve;

use DateTime;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use App\Entity\Prof;
use App\Entity\Eleve;

use App\Entity\Message;
use App\Entity\Session;
use App\Form\MessageType;
use App\Entity\CreneauCours;
use App\Form\CreationCoursType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/eleve")
 */
class EleveController extends AbstractController
{
    // /**
    //  * @Route("/calendar", name="cours_calendar")
    //  */
    // public function calendar() {
    //     return $this->render('course/calendar.html.twig', [
    //         'title' => 'Planning'
    //     ]);
    // }

    /**
     * @Route("/sendMessageEleve/{idProf}/{idEleve}", name="send_message_eleve")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function sendMessageEleve(Prof $prof, Eleve $eleve, Request $request)
    {

        $form = $this->createForm(MessageType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $message = new Message();
            $message->setProf($prof);
            $message->setEleve($eleve);
            $message->setAuteur($eleve->getUsername());
            $message->setContenu($form->get("contenu")->getData());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('home_eleve');
        }

        return $this->render('message/sendMessage.html.twig', [
            'messageForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/showMessagesEleve", name="show_messages_eleve")
     */
    public function showMessagesEleve() {

        return $this->render('eleve/showMessageEleve.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * @Route("/conversationEleve/{idProf}", name="conversation_eleve")
     * @ParamConverter("prof", options={"id" = "idProf"})
     */
    public function conversationEleve(Prof $prof) {

        return $this->render('eleve/conversationEleve.html.twig', [
            'prof' => $prof,
        ]);
    }
}

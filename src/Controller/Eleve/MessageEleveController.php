<?php

namespace App\Controller\Eleve;

use App\Entity\Prof;
use App\Entity\Eleve;
use App\Entity\Message;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Controller\Eleve\EleveController;

/**
 * @Route("/eleve")
 */
class MessageEleveController extends EleveController
{
    /**
     * Liste des conversations d'un élève
     * @Route("/showMessagesEleve/{id}", name="show_messages_eleve")
     */
    public function showMessagesEleve(Eleve $eleve) {
        $this->setNbMsgNonLus();

        // Conversations entre le prof et chaque eleve
        $allConversations = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findAllConversationsEleve($eleve);

        // tableau [ [prof, nombreMessagesNonLus],  [prof, nombreMessagesNonLus], ...] 
        $allConversationsNbMsgNonLus = [];

        foreach($allConversations as $conversation){
            $prof =  $conversation->getProf();

            $nbMsgNonLus = $this->getDoctrine()
                ->getRepository(Message::class)
                ->findNbNonLusEleveProf($prof, $eleve);

            // On ajoute l'élève et le nombre de messages non lus
            array_push($allConversationsNbMsgNonLus, ['prof' => $prof, 'nbMsg' => $nbMsgNonLus]);      
        }

        return $this->render('eleve/showMessageEleve.html.twig', [
            'allConversations' => $allConversationsNbMsgNonLus
        ]);
    }

    /**
     * Conversation avec un prof
     * @Route("/conversationEleve/{idEleve}/{idProf}/", name="conversation_eleve")
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     * @ParamConverter("prof", options={"id" = "idProf"})
     */
    public function conversationEleve(Eleve $eleve, Prof $prof) {
        $this->setNbMsgNonLus();

        $session = new SessionUser();

        $allMsg = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversation($eleve, $prof);

        $msgLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationLusEleve($eleve, $prof);

        $msgNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationNonLusEleve($eleve, $prof);

        $msgEnvoyes = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationEnvoyesEleve($eleve, $prof);

        $entityManager = $this->getDoctrine()->getManager();

        foreach($msgNonLus as $message){
            $message->setLu(true);
            $entityManager->persist($message);
        }

        $nbMessagesNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findNbNonLusEleve($eleve);

        // $eleve->setNbMsgNonLus($nbMessagesNonLus);

        // $entityManager->persist($eleve);
        // $session->set('nbMsgNonLus', $nbMessagesNonLus);

        $entityManager->flush();

        return $this->render('eleve/conversationEleve.html.twig', [
            'prof' => $prof,
            'eleve' => $eleve,
            'allMsg' => $allMsg,
            'msgLus' => $msgLus,
            'msgNonLus' => $msgNonLus,
            'msgEnvoyes' => $msgEnvoyes
        ]);
    }

    /**
     * Refresh en cas de nouveau message     
     * @Route("/conversationEleve/{idEleve}/{idProf}/refreshMsg", name="conversation_eleve_refresh_msg")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function refreshMsgEleve(Prof $prof, Eleve $eleve) {
    
        $msgNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationNonLusEleve($eleve, $prof);

        $nouveauMessage = false;

        if ($msgNonLus){
            $nouveauMessage = true;
        }   

        return $this->render('prof/test.html.twig', [
            'prof' => $prof,
            'eleve' => $eleve,
            'nouveauMessage' => $nouveauMessage,
        ]);
    }

    /**
     * Envoi de message
     * @Route("/sendMessageEleve/{idProf}/{idEleve}", name="send_message_eleve")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function sendMessageEleve(Prof $prof, Eleve $eleve)
    {
        $this->setNbMsgNonLus();
        
        $contenu = $_POST['text'];
        $message = new Message();
        $message->setProf($prof);
        $message->setEleve($eleve);
        $message->setAuteur($eleve->getUsername());
        $message->setContenu($contenu);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($message);
        $entityManager->flush();

        return $this->redirectToRoute('conversation_eleve', ['idProf' => $prof->getId(), 'idEleve' => $eleve->getId()]);
    }
}

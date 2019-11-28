<?php

namespace App\Controller\Eleve;

use DateTime;
use App\Entity\Prof;
use App\Entity\Message;
use App\Controller\Eleve\EleveController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/eleve")
 */
class MessageEleveController extends EleveController
{
    /**
     * Liste des conversations d'un élève
     * @Route("/showMessagesEleve/", name="show_messages_eleve")
     */
    public function showMessagesEleve() {

        $eleve = $this->getUser();

        // Conversations entre le prof et chaque eleve
        $allConversations = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findAllConversationsEleve($eleve);

        // tableau [ [prof, nombreMessagesNonLus],  [prof, nombreMessagesNonLus], ...] 
        $allConversationsNbMsgNonLus = [];

        $date = new DateTime('now');
        $date->add(new \DateInterval('PT1H'));

        foreach($allConversations as $conversation){
            $prof =  $conversation->getProf();

            $nbMsgNonLus = $this->getDoctrine()
                ->getRepository(Message::class)
                ->findNbNonLusEleveProf($prof, $eleve);

            $dernierMessage = $this->getDoctrine()
                ->getRepository(Message::class)
                ->findDernierMessageEleve($eleve, $prof);

            if ($dernierMessage){
                // $differenceDate = date_diff($date, $dernierMessage->getDateEnvoi())->format("%d jours, %h h, %i m, %s s");
                $differenceDate = date_diff($date, $dernierMessage->getDateEnvoi())->format("%d jours, %h h, %i m, %s s");
            }
            else $differenceDate = null;

            // On ajoute l'élève et le nombre de messages non lus
            array_push($allConversationsNbMsgNonLus, ['prof' => $prof, 'nbMsg' => $nbMsgNonLus, 'dernierMsg' => $dernierMessage, 'dateDiff' => $differenceDate]);         
        }

        return $this->render('eleve/showMessageEleve.html.twig', [
            'allConversations' => $allConversationsNbMsgNonLus
        ]);
    }

    /**
     * Conversation avec un prof
     * @Route("/conversationEleve/{id}/", name="conversation_eleve")
     */
    public function conversationEleve(Prof $prof) {

        $eleve = $this->getUser();

        $allMsg = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversation($eleve, $prof);

        $msgLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationLusEleve($eleve, $prof);

        $msgNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationNonLusEleve($eleve, $prof);

        $entityManager = $this->getDoctrine()->getManager();

        foreach($msgNonLus as $message){
            $message->setLu(true);
            $entityManager->persist($message);
        }

        $entityManager->flush();

        $nbMessagesNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findNbNonLusEleve($eleve);

        $eleve->setNbMsgNonLus($nbMessagesNonLus);

        $entityManager->persist($eleve);

        $entityManager->flush();

        return $this->render('eleve/conversationEleve.html.twig', [
            'prof' => $prof,
            'eleve' => $eleve,
            'allMsg' => $allMsg,
            'msgLus' => $msgLus,
            'msgNonLus' => $msgNonLus,
        ]);
    }

    /**
     * Refresh en cas de nouveau message     
     * @Route("/conversationEleve/{idProf}/refreshMsgEleve", name="conversation_eleve_refresh_msg")
     * @ParamConverter("prof", options={"id" = "idProf"})
     */
    public function refreshMsgEleve(Prof $prof) {
    
        $eleve = $this->getUser();

        $msgNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationNonLusEleve($eleve, $prof);

        $nouveauMessage = false;

        if ($msgNonLus){
            $nouveauMessage = true;
        }   

        return $this->render('refresh.html.twig', [
            'nouveauMessage' => $nouveauMessage,
        ]);
    }

    /**
     * Envoi de message
     * @Route("/sendMessageEleve/{id}/", name="send_message_eleve")
     */
    public function sendMessageEleve(Prof $prof)
    {       
        $eleve = $this->getUser();

        $contenu = $_POST['text'];
        $message = new Message();
        $message->setProf($prof);
        $message->setEleve($eleve);
        $message->setAuteur($eleve->getUsername());
        $message->setContenu($contenu);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($message);
        $prof->setNbMsgNonLus($prof->getNbMsgNonLus()+1);
        $entityManager->flush();

        return $this->redirectToRoute('conversation_eleve', ['id' => $prof->getId()]);
    }
}

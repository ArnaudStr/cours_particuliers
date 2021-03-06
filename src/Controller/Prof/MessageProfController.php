<?php

namespace App\Controller\Prof;

use App\Entity\Eleve;
use App\Entity\Message;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Controller\Prof\ProfController;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;



/**
 * @Route("/prof")
 * @IsGranted("ROLE_PROF")
 */
class MessageProfController extends ProfController
{
    /**
     * Liste des conversations du prof
     * @Route("/showMessagesProf", name="show_messages_prof")
     */
    public function showMessagesProf() {

        $prof = $this->getUser();

        // Conversations entre le prof et chaque eleve
        $allConversations = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findAllConversationsProf($prof);

        // tableau [ [eleve, nombreMessagesNonLus],  [eleve, nombreMessagesNonLus], ...] 
        $allConversationsNbMsgNonLus = [];

        $date = new DateTime('now');
        // pour avoir la bonne date en France
        $date->add(new \DateInterval('PT1H'));

        foreach($allConversations as $conversation){
            $eleve =  $conversation->getEleve();


            $dernierMessage = $this->getDoctrine()
                ->getRepository(Message::class)
                ->findDernierMessageProf($eleve, $prof);
            
            $differenceDate = date_diff($date, $dernierMessage->getDateEnvoi())->format("%d jours, %h h, %i m, %s s");

            $nbMsgNonLus = $this->getDoctrine()
                ->getRepository(Message::class)
                ->findNbNonLusProfEleve($prof, $eleve);

            // On ajoute l'élève et le nombre de messages non lus
            array_push($allConversationsNbMsgNonLus, ['eleve' => $eleve, 'nbMsg' => $nbMsgNonLus, 'dernierMsg' => $dernierMessage, 'dateDiff' => $differenceDate]);      
        }

        return $this->render('prof/showMessageProf.html.twig', [
            'allConversations' => $allConversationsNbMsgNonLus,
            'title' => 'Messagerie'
        ]);
    }
    
    /**
     * Conversation avec un élève
     * @Route("/conversationProf/{id}/", name="conversation_prof")
     */
    public function conversationProf(Eleve $eleve) {

        $prof = $this->getUser();

        $allMsg = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversation($eleve, $prof);

        $msgLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationLusProf($eleve, $prof);

        $msgNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationNonLusProf($eleve, $prof);

        $entityManager = $this->getDoctrine()->getManager();

        foreach($msgNonLus as $message){
            $message->setLu(true);
            $entityManager->persist($message);
        }

        $entityManager->flush();

        $nbMessagesNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findNbNonLusProf($prof);

        $prof->setNbMsgNonLus($nbMessagesNonLus);

        $entityManager->persist($prof);

        $entityManager->flush();

        return $this->render('prof/conversationProf.html.twig', [
            'prof' => $prof,
            'eleve' => $eleve,
            'allMsg' => $allMsg,
            'msgLus' => $msgLus,
            'msgNonLus' => $msgNonLus,
            'title ' => ''.$eleve
        ]);
    }

    /**     
     * Refresh en cas de nouveau message reçu
     * @Route("/conversationProf/{idEleve}/refreshMsgProf", name="conversation_prof_refresh_msg")
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function refreshMsgProf(Eleve $eleve) {
    
        $prof = $this->getUser();

        $msgNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationNonLusProf($eleve, $prof);

        $nouveauMessage = false;

        if ($msgNonLus){
            $nouveauMessage = true;
        }   

        return $this->render('refresh.html.twig', [
            'nouveauMessage' => $nouveauMessage,
        ]);
    }

    /**
     * Envoi d'un message à un élève
     * @Route("/sendMessageProf/{id}", name="send_message_prof")
     */
    public function sendMessageProf(Eleve $eleve)
    {
        $prof = $this->getUser();

        $contenu = $_POST['text'];
        $message = new Message();
        $message->setProf($prof);
        $message->setEleve($eleve);
        $message->setAuteur($prof->getUsername());
        $message->setContenu($contenu);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($message);
        $entityManager->flush();

        return $this->redirectToRoute('conversation_prof', ['id' => $eleve->getId()]);
    }
}

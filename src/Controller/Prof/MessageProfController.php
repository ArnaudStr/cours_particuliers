<?php

namespace App\Controller\Prof;

use App\Entity\Prof;
use App\Entity\Eleve;
use App\Entity\Message;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Controller\Prof\ProfController;


/**
 * @Route("/prof")
 */
class MessageProfController extends ProfController
{
    /**
     * Liste des conversations du prof
     * @Route("/showMessagesProf/{id}", name="show_messages_prof")
     */
    public function showMessagesProf(Prof $prof) {
        $this->setNbMsgNonLus();

        // Conversations entre le prof et chaque eleve
        $allConversations = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findAllConversationsProf($prof);

        // tableau [ [eleve, nombreMessagesNonLus],  [eleve, nombreMessagesNonLus], ...] 
        $allConversationsNbMsgNonLus = [];

        foreach($allConversations as $conversation){
            $eleve =  $conversation->getEleve();

            $nbMsgNonLus = $this->getDoctrine()
                ->getRepository(Message::class)
                ->findNbNonLusProfEleve($prof, $eleve);

            // On ajoute l'élève et le nombre de messages non lus
            array_push($allConversationsNbMsgNonLus, ['eleve' => $eleve, 'nbMsg' => $nbMsgNonLus]);      
        }

        return $this->render('prof/showMessageProf.html.twig', [
            'allConversations' => $allConversationsNbMsgNonLus
        ]);
    }
    
    /**
     * Conversation avec un élève
     * @Route("/conversationProf/{idProf}/{idEleve}/", name="conversation_prof")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function conversationProf(Prof $prof, Eleve $eleve) {
        $this->setNbMsgNonLus();

        $session = new SessionUser();

        $allMsg = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversation($eleve, $prof);

        $msgLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationLusProf($eleve, $prof);

        $msgNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationNonLusProf($eleve, $prof);

        $msgEnvoyes = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationEnvoyesProf($eleve, $prof);

        $entityManager = $this->getDoctrine()->getManager();

        foreach($msgNonLus as $message){
            $message->setLu(true);
            $entityManager->persist($message);
        }

        $nbMessagesNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findNbNonLusProf($prof);

        $session->set('nbMsgNonLus', $nbMessagesNonLus);

        $entityManager->flush();

        return $this->render('prof/conversationProf.html.twig', [
            'prof' => $prof,
            'eleve' => $eleve,
            'allMsg' => $allMsg,
            'msgLus' => $msgLus,
            'msgNonLus' => $msgNonLus,
            'msgEnvoyes' => $msgEnvoyes
        ]);
    }

    /**     
     * Refresh en cas de nouveau message reçu
     * @Route("/conversationProf/{idProf}/{idEleve}/refreshMsg", name="conversation_prof_refresh_msg")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function refreshMsgProf(Prof $prof, Eleve $eleve) {
    
        $msgNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationNonLusProf($eleve, $prof);

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
     * Envoi d'un message à un élève
     * @Route("/sendMessageProf/{idProf}/{idEleve}", name="send_message_prof")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function sendMessageProf(Prof $prof, Eleve $eleve)
    {
        $this->setNbMsgNonLus();

        $contenu = $_POST['text'];
        $message = new Message();
        $message->setProf($prof);
        $message->setEleve($eleve);
        $message->setAuteur($prof->getUsername());
        $message->setContenu($contenu);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($message);
        $entityManager->flush();

        return $this->redirectToRoute('conversation_prof', ['idProf' => $prof->getId(), 'idEleve' => $eleve->getId()]);
    }
}
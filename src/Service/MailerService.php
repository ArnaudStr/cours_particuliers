<?php
namespace App\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
class MailerService extends AbstractController
{

    private $mailer;
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendToken($token, $to, $username, $template)
    // public function sendToken($token, $to, $username, $template)
    {
        $message = (new \Swift_Message('Mail de confirmation'))
            ->setFrom('arnaud.straumann@free.fr')
            // ->setTo($to)
            ->setTo('arnaud.straumann@free.fr')
            ->setBody(
                // $this->renderView(
                //     'emails/'.$template,
                //     [
                //         'token' => $token,
                //         'username' => $username
                //     ]
                // ),
                // 'text/html'
                'wesh'
            )
        ;
        $this->mailer->send($message);
    }
}

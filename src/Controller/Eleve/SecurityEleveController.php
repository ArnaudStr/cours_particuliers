<?php

namespace App\Controller\Eleve;

use DateTime;
use DateTimeZone;
use App\Entity\Eleve;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use App\Controller\Eleve\EleveController;

/**
 * @Route("/eleve")
 */
class SecurityEleveController extends EleveController
{
    /**
     * @Route("/loginEleve", name="login_eleve")
     */
    public function loginEleve(AuthenticationUtils $authenticationUtils) {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/loginEleve.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logoutEleve", name="logout_eleve")
     */
    public function logoutEleve() {
        return $this->redirectToRoute("search_course");
    }

     /**
     * @Route("/forgottenPasswordEleve", name="forgotten_password_eleve")
     */
    public function forgottenPasswordEleve(
        Request $request,
        \Swift_Mailer $mailer,
        TokenGeneratorInterface $tokenGenerator
    )
    {
 
        if ($request->isMethod('POST')) {
 
            $email = $request->request->get('email');
 
            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(Eleve::class)->findOneByEmail($email);
            /* @var $user User */
 
            if ($user === null) {
                $this->addFlash('forgotPwd', 'Email Inconnu');
                return $this->redirectToRoute('login_eleve');
            }
            $token = $tokenGenerator->generateToken();
 
            try{
                $user->setToken($token);
                $date = new DateTime('now',new DateTimeZone('Europe/Paris'));
                $date->add(new \DateInterval('P1D'));
    
                $user->setTokenExpire(
                    $date
                );
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('forgotPwd', $e->getMessage());
                return $this->redirectToRoute('login_eleve');
            }
 
            $url = $this->generateUrl('reset_password_eleve', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            $message = (new \Swift_Message('Forgot Password'))
                ->setFrom('arnaud6757@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    "Voici le lien pour réinitialiser votre mot de passe : <a href='". $url ."'>Réinitialiser mon mot de passe</a>",
                    'text/html'
                );
 
            $mailer->send($message);

            $this->addFlash('forgotPwd', 'Vous avez reçu un email pour changer votre mot de passe!');

            return $this->redirectToRoute('login_eleve');
        }
 
        return $this->render('security/forgotten_password.html.twig');
    }

    /**
     * @Route("/resetPasswordEleve/{token}", name="reset_password_eleve")
     */
    public function resetPasswordEleve(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($request->isMethod('POST')) {
            $entityManager = $this->getDoctrine()->getManager();
 
            $user = $entityManager->getRepository(Eleve::class)->findOneByToken($token);
            /* @var $user User */
 
            if ($user === null) {
                $this->addFlash('resetPwd', 'Token Inconnu');
                return $this->redirectToRoute('login_eleve');
            }
            else if ($user->getTokenExpire()<new DateTime('now',new DateTimeZone('Europe/Paris'))){
                $this->addFlash('resetPwd', 'Votre token de changement de mot de passe a expiré');
                return $this->redirectToRoute('login_eleve');
            }
 
            $user->setToken(null);
            $user->setTokenExpire(null);
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $entityManager->flush();
 
            $this->addFlash('resetPwd', 'Mot de passe mis à jour');
 
            return $this->redirectToRoute('login_eleve');
        }else {
 
            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }
    }
}

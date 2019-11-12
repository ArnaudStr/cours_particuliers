<?php

namespace App\Controller\Prof;

use DateTime;
use DateTimeZone;
use App\Entity\Prof;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use App\Controller\Prof\ProfController;

/**
 * @Route("/prof")
 */
class SecurityProfController extends ProfController
{
    /**
     * @Route("/loginProf", name="login_prof")
     */
    public function loginProf(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/loginProf.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logoutProf", name="logout_prof")
     */
    public function logoutProf() {
        
        $this->clear();

        return $this->redirectToRoute("search_course");
    }

    /**
     * @Route("/forgottenPasswordProf", name="forgotten_password_prof")
     */
    public function forgottenPasswordProf(
        Request $request,
        \Swift_Mailer $mailer,
        TokenGeneratorInterface $tokenGenerator
    )
    {
 
        if ($request->isMethod('POST')) {
 
            $email = $request->request->get('email');
 
            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(Prof::class)->findOneByEmail($email);
            /* @var $user User */
 
            if ($user === null) {
                $this->addFlash('danger', 'Email Inconnu');
                return $this->redirectToRoute('home');
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
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('home');
            }
 
            $url = $this->generateUrl('reset_password_prof', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            $message = (new \Swift_Message('Forgot Password'))
                ->setFrom('arnaud6757@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    "Voici le lien pour réinitialiser votre mot de passe : <a href='". $url ."'>Réinitialiser mon mot de passe</a>",
                    'text/html'
                );
 
            $mailer->send($message);

            $this->addFlash('notice', 'Mail envoyé');

            return $this->redirectToRoute('login_prof');
        }
 
        return $this->render('security/forgotten_password.html.twig');
    }

     /**
     * @Route("/resetPasswordProf/{token}", name="reset_password_prof")
     */
    public function resetPasswordProf(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
 
        if ($request->isMethod('POST')) {
            $entityManager = $this->getDoctrine()->getManager();
 
            $user = $entityManager->getRepository(Prof::class)->findOneByToken($token);
            /* @var $user User */
 
            if ($user === null) {
                $this->addFlash('danger', 'Token Inconnu');
                return $this->redirectToRoute('login_prof');
            }
            else if ($user->getTokenExpire()<new DateTime('now',new DateTimeZone('Europe/Paris'))){
                $this->addFlash('danger', 'Votre token de changement de mot de passe a expiré');
                return $this->redirectToRoute('login_prof');
            }
 
            $user->setToken(null);
            $user->setTokenExpire(null);
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $entityManager->flush();
 
            $this->addFlash('notice', 'Mot de passe mis à jour');
 
            return $this->redirectToRoute('login_prof');
        }else {
 
            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }
    }
}

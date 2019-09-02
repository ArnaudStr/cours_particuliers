<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MemberController extends AbstractController
{

    /**
     * @Route("/prof/profile", name="profile_prof")
     */
    public function profileProf()
    {
        return $this->render('member/profile.html.twig', [
            'controller_name' => 'MemberController',
        ]);
    }

    /**
     * @Route("/eleve/profile", name="profile_eleve")
     */
    public function profileEleve()
    {
        return $this->render('member/profile.html.twig', [
            'controller_name' => 'MemberController',
        ]);
    }
}

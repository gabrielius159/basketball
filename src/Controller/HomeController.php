<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        if($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('team');
        }
        
        return $this->render('home/index.html.twig', [
        ]);
    }

    /**
     * @Route("/change-mode", methods={"GET"}, name="change_template_mode")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function changeTemplateModeAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $lightMode = $request->getSession()->get('lightMode');

        if(!$lightMode) {
            $request->getSession()->set('lightMode', true);

            return $this->redirect($request->headers->get('referer'));
        }

        switch ($lightMode) {
            case true: {
                $request->getSession()->set('lightMode', false);

                break;
            }
            case false: {
                $request->getSession()->set('lightMode', true);

                break;
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }
}

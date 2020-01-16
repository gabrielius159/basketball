<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BaseController
 *
 * @package App\Controller
 */
abstract class BaseController extends AbstractController
{
    /**
     * @return User
     */
    protected function getUser(): User
    {
        return parent::getUser();
    }

    /**
     * @param Request $request
     * @param string $darkMode
     * @param string $lightMode
     *
     * @return string
     */
    protected function getTemplateByTemplateMode(Request $request, string $darkMode, string $lightMode): string
    {
        $mode = $request->getSession()->get('lightMode');

        if($mode == null) {
            return $darkMode;
        }

        switch ($mode) {
            case true: {

                return $lightMode;
            }
            case false: {

                return $darkMode;
            }
        }

        return $darkMode;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isUserInLightMode(Request $request): bool
    {
        $mode = $request->getSession()->get('lightMode');

        if($mode == null) {
            return false;
        }

        switch ($mode) {
            case true: {

                return true;
            }
            case false: {

                return false;
            }
        }

        return false;
    }
}

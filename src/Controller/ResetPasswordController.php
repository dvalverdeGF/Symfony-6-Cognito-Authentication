<?php

namespace App\Controller;

use App\Form\RequestPasswordType;
use App\Form\ResetPasswordType;
use App\Security\CognitoResetPassword;
use Aws\Result;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request, CognitoResetPassword $cognitoResetPassword): Response
    {
        $form = $this->createForm(RequestPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $cognitoResetPassword->submitForgotPassword($form->getData());
            if ($result instanceof Result) {
                $this->addFlash('success', 'Please check your email. Shortly, you will receive a verification code to reset your password. Don\'t forget to check your spam folder!');
                return $this->redirectToRoute('app_reset_password');
            } else {
                $this->addFlash('error', 'There was an error sending the reset link: '.$result);
            }
        }
        return $this->render('reset_password/forgot_password.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
    #[Route('/reset-password', name: 'app_reset_password')]
    public function resetPassword(Request $request, CognitoResetPassword $cognitoResetPassword): Response
    {
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $cognitoResetPassword->submitPasswordReset($form->getData());
            if ($result instanceof Result) {
                $this->addFlash('success', 'Your password has been reset.');
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('error', 'There was an error resetting your password: '.$result);
            }
        }
        return $this->render('reset_password/reset_password.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }


}

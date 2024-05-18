<?php

namespace App\Controller;

use App\Entity\Form;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;

class FormController extends AbstractController
{
    #[Route('/', name: 'app_form', methods: ['GET', 'POST'])]
    public function showForm(Request $request, MailerInterface $mailer, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $formEntity = new Form();

        if ($request->isMethod('POST')) {
            $formEntity->setName($request->request->get('name'));
            $formEntity->setSujet($request->request->get('sujet'));
            $formEntity->setMessage($request->request->get('message'));
          
            // Vérifier si l'email n'est pas null avant de l'utiliser
            $email = $request->request->get('email');
            if ($email !== null) {
                try {
                    $email = (new Email())
                        ->subject('Nouveau message de ' . $formEntity->getName())
                        ->from($email)
                        ->to('nourlouhichi318@email.com')
                        ->text($formEntity->getMessage());
                  
                    $mailer->send($email);

                    // Enregistrer l'entité du formulaire dans la base de données
                    $entityManager->persist($formEntity);
                    $entityManager->flush();

                    $this->addFlash('success', 'Email envoyé avec succès !');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de l\'email.');
                }
            } else {
                $this->addFlash('error', 'L\'adresse e-mail est vide.');
            }

            return $this->redirectToRoute('app_form');
        }

        return $this->render('vinyl/Form.html.twig', [
            'errors' => []
        ]);
    }
}

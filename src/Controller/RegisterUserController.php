<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegisterType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Services\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
class RegisterUserController extends AbstractController
{
    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/auth/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function newUser(Request $request, UserRepository $userRepository , SluggerInterface $slugger): Response
    {
        $user = new User();
        $form = $this->createForm(UserRegisterType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $email = (new Email())
                ->from('sportifyapp00@gmail.com')
                ->to ('sayed.benslimene@esprit.tn')
                ->subject('registration done')
                ->text('vous avez registrez seuccsfly');
            $transport = new GmailSmtpTransport('sportifyapp00@gmail.com','rulrljfrzqctiqcd');
            $mailer=new Mailer($transport);
            $mailer->send($email);

            $user->setRoles(['ROLE_USER']);
            $plainpwd = $user->getPassword();
            $encoded = $this->passwordEncoder->encodePassword($user, $plainpwd);
            $user->setPassword($encoded);
            $photo = $form->get('photo')->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photo) {
                $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photo->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $photo->move(
                        $this->getParameter('users_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $user->setImage($newFilename);
            }

            $userRepository->save($user, true);
//            $message = " a été ajouté avec succès";
//            $mailMessage=$user->getNom().' '.$user->getPrenom().' '.$message;
//            $mailer->sendEmail(content: $mailMessage);


            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('register_user/index.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

}

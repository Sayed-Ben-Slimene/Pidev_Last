<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

use MercurySeries\FlashyBundle\FlashyNotifier;

use App\Services\MailerService;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route(path: '/auth')]
class SecurityController extends AbstractController
{
    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
//         if ($this->getUser()) {
//             return $this->redirectToRoute('homepage');
//         }
        if ($this->getUser() ) {
            if ($this->getUser()->getRoles() === ["ROLE_USER"]) {
                return $this->redirectToRoute('homepage');
            }
            if ($this->getUser()->getRoles() === ["ROLE_ORGANISATOR"]) {
                return $this->redirectToRoute('app_admin');
            }
            if ($this->getUser()->getRoles() === ["ROLE_ADMIN"]) {
                return $this->redirectToRoute('app_admin');
            }
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route(path: '/forgotpass', name: 'app_forgot_Pass')]

    public function forgotPassword(Request $request, UserRepository $userRepository, TokenGeneratorInterface  $tokenGenerator, SluggerInterface $slugger,FlashyNotifier $flashy)
    {

        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $donnees = $form->getData();//


            $user = $userRepository->findOneBy(['email'=>$donnees]);
            if(!$user) {
                $this->addFlash('danger','cette adresse n\'existe pas');
                return $this->redirectToRoute("app_forgot_Pass");

            }
            $token = $tokenGenerator->generateToken();

            try{
                $user->setResetToken($token);
                $entityManger = $this->getDoctrine()->getManager();
                $entityManger->persist($user);
                $entityManger->flush();


            }catch(\Exception $exception) {
                $this->addFlash('warning','une erreur est survenue :'.$exception->getMessage());
                return $this->redirectToRoute("app_login");

            }

            $url = $this->generateUrl('app_reset_password',array('token'=>$token),UrlGeneratorInterface::ABSOLUTE_URL);

            //BUNDLE MAILER
            $email = (new Email())
                ->from('sportifyapp00@gmail.com')
                ->to($user->getEmail())
                ->subject('Mot de password oublié')
                ->text('vous avez registrez seuccsfly')
                ->html("<p> Bonjour</p> unde demande de réinitialisation de mot de passe a été effectuée. Veuillez cliquer sur le lien suivant :".$url,
                "text/html");
            //send mail
            $transport = new GmailSmtpTransport('sportifyapp00@gmail.com','rulrljfrzqctiqcd');
            $mailer=new Mailer($transport);
            $mailer->send($email);
            $this->addFlash('message','E-mail  de réinitialisation du mp envoyé :');
            $flashy->success('E-mail  de réinitialisation du mp envoyé !!');

            //    return $this->redirectToRoute("app_login");

        }

        return $this->render("security/forgotPassword.html.twig",['form'=>$form->createView()]);
    }
    #[Route(path: '/resetpassword/{token}', name: 'app_reset_password')]
    public function resetpassword(Request $request,string $token, UserPasswordEncoderInterface $passwordEncoder,FlashyNotifier $flashy)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['reset_token'=>$token]);

        if($user === null ) {
            $this->addFlash('danger','TOKEN INCONNU');
            return $this->redirectToRoute("app_login");

        }

        if($request->isMethod('POST')) {
            $user->setResetToken(null);

            $user->setPassword($this->passwordEncoder->encodePassword($user,$request->request->get('password')));

            $entityManger = $this->getDoctrine()->getManager();
            $entityManger->persist($user);
            $entityManger->flush();

            $this->addFlash('message','Mot de passe mis à jour :');
            $flashy->success('E-mail  de réinitialisation du mp envoyé !!');

            return $this->redirectToRoute("app_login");

        } else {
            return $this->render("security/resetPassword.html.twig",['token'=>$token]);

        }
    }




}

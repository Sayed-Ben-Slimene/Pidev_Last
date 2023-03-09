<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;

#[Route('/admin')]
class AdminPanelController extends AbstractController
{
    private UserPasswordEncoderInterface $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin_panel/PageHomeAdmin.html.twig');
    }

    #[Route('/loginAuth', name: 'Auth_login')]
    public function loginAuth(): Response
    {
        return $this->render('admin_panel/loginAuth.html.twig');
    }

    #[Route('/neworganisator', name: 'app_organisator_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository , SluggerInterface $slugger,FlashyNotifier $flashy): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $flashy->success('E-mail  de réinitialisation du mp envoyé !!');

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
            return $this->redirectToRoute('Organisateurlist', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('admin_panel/organisator/newOrganisator.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/registerAuth', name: 'Auth_register')]
    public function registerAuth(Request $request, UserRepository $userRepository , SluggerInterface $slugger): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user->setRoles(['ROLE_ORGANISATOR']);

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
            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('admin_panel/registerAuth.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[Route('/Auth_forgot_pass', name: 'Auth_forgot_pass')]
    public function forgot_passAuth(): Response
    {
        return $this->render('admin_panel/Auth_forgot_pass.html.twig');
    }


    #[Route('/organisateurList', name: 'Organisateurlist',  methods: ['GET'])]
    public function Organisateurlist(UserRepository $userRepository): Response
    {
        $users = $userRepository->findByRole('ROLE_ORGANISATOR');

        return $this->render('admin_panel/organisator/OrganisateurList.html.twig', [
            'users' => $users,
        ]);
    }


    #[Route('/{id}', name: 'app_organisator_show', methods: ['GET'])]
    public function show(User $user): Response
    {

        return $this->render('admin_panel/organisator/showorganisator.html.twig', [
            'user' => $user,

        ]);
    }


    #[Route('/{id}', name: 'app_organisator_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('Organisateurlist', [], Response::HTTP_SEE_OTHER);
    }

    /**              Fans    End                         */

    #[Route('/{id}/editOrganisator', name: 'app_organisator_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository , SluggerInterface $slugger): Response
    {
        $form = $this->createForm(UserType::class, $user );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

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

            return $this->redirectToRoute('Organisateurlist', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin_panel/organisator/EditOrganisator.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }









}

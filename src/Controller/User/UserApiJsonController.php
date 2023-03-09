<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Repository\UserRepository;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('/api')]
class UserApiJsonController extends AbstractController
{

    #[Route('/get', name: 'app_User_services',methods:'GET')]
    public function getUsers(UserRepository $repo,SerializerInterface $serializerInterface):Response
    {
        $user=$repo->findall();
        $json=$serializerInterface->serialize($user,'json',['groups'=>'users']);
        return new Response ($json,200,[
            "content-Type"=>"application/json"
        ]);

    }
    #[Route('/add', name: 'add_user_services',methods:'POST')]

    public function addUsers(Request $request,SerializerInterface $serializer,EntityManagerInterface $em): JsonResponse
    {
        $content = $request->getcontent();
        $post = $serializer->deserialize($content, User::class, 'json');

        $em->persist($post);
        $em->flush();


        return $this->json($post, 201, [], ['groups' => 'users']);
    }


    #[Route('/delete/{id}', name: 'delete_user_services', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em, $id): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException(
                'Ne pas User trouber pour id '.$id
            );
        }
        $em->remove($user);
        $em->flush();
        return $this->json(['message' => 'user supprimer'], 200);
    }

    #[Route('/update/{id}', name: 'update_user_services', methods: ['PUT'])]
    public function updateUser(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, $id): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        // désérialisation de la requête
        $content = $request->getContent();
        $updatedUser = $serializer->deserialize($content, User::class, 'json');

        // si la catégorie est différente, on l'ajoute à la base de données
        if (!$em->contains($user)) {
            $em->persist($user);
        }

        // mise à jour des propriétés du produit
        $user->setNom($updatedUser->getNom());
        $user->setPrenom($updatedUser->getPrenom());
        $user->setEmail($updatedUser->getEmail());
        $user->setPassword($updatedUser->getPassword());
        $user->setAdress($updatedUser->getAdress());
        $user->setTel($updatedUser->getTel());
        $user->setImage($updatedUser->getImage());


        // association avec la nouvelle catégorie
        // mise à jour de la base de données
        $em->flush();

        return $this->json($user, 200, [], ['groups' => 'users']);
    }

    /******************Detail Reclamation*****************************************/
    //Detail Reclamation
//    #[Route('{id}', name: 'show_user', methods: ['GET'])]
//
//    public function detailUser(Request $request)
//    {
//        $id = $request->get("id");
//
//        $em = $this->getDoctrine()->getManager();
//        $reclamation = $this->getDoctrine()->getManager()->getRepository(User::class)->find($id);
//        $encoder = new JsonEncoder();
//        $normalizer = new ObjectNormalizer();
//        $normalizer->setCircularReferenceHandler(function ($object) {
//            return $object->getDescription();
//        });
//        $serializer = new Serializer([$normalizer], [$encoder]);
//        $formatted = $serializer->normalize($reclamation);
//        return new JsonResponse($formatted);
//    }

    public function ImagesUserAction(Request $request)
    {
        $publicResourcesFolderPath = $this->get('kernel')->getRootDir() . '/../web/users_photo/ ';
        $image = $request->query->get("photo");
        // This should return the file located in /mySymfonyProject/web/public-resources/TextFile.txt
        // to being viewed in the Browser
        return new BinaryFileResponse($publicResourcesFolderPath . $image);
    }



    #[Route('/update/{id}', name: 'update_user_services', methods: ['PUT'])]
    public function EditUserAction(Request $request): Response
    {
        $id = $request->get("id");
        $nom= $request->get("nom");
        $prenom= $request->get("prenom");
        $adress= $request->get("adress");
        $password = $request->get("password");
        $email = $request->get("email");
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);
        if ($request->files->get("photo") != null) {
            $file = $request->files->get("photo");
            $fileName = $file->getClientOriginalName();

            // moves the file to the directory where brochures are stored
            $file->move(
                $fileName
            );
            $user->setPhoto($fileName);
        }

        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setPlainPassword($password);
        $user->setEmail(urldecode($email));
        $user->setPassword(urldecode($password));
        $user->setAdress(urldecode($adress));

        try {

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return new Response("success");


        } catch (Exception $ex) {
            return new Response("fail");
        }

    }




    public function GetUserbyIdAction(Request $request)
    {
        $user = $this->getDoctrine()->getManager()->getRepository(User::class)
            ->find($request->get('id'));


        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($user);
        return new JsonResponse($formatted);
    }




    public function GetPassbyEmailAction(Request $request): JsonResponse|Response
    {        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['email' => $request->get('email')]);


        if ( $user ==null)
        {


        }
        else {


            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize($user->getPassword());
            return new JsonResponse($formatted);
        }
        return new Response("fail");





    }

    //***********Login******************************//
    #[Route('/loginuser', name: 'loginuser', methods: ['GET'])]

    public function loginAction(Request $request)
    {
        $email = $request->query->get("email");
        $password = $request->query->get("password");
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        // $user->setPlainPassword($user->getPlainPassword());
        if($user==null) {

        }
        if ($user) {
            if (password_verify($password, $user->getPassword())) {
                $serializer = new Serializer([new ObjectNormalizer()]);
                $formatted = $serializer->normalize($user);
                return new JsonResponse($formatted);
            } else {
                return new Response("failed");
            }
        } else {
            return new Response("failed");
        }

    }

    //*********Register***************************//
    #[Route('/registeruser', name: 'registeruser',methods:'POST')]

    public function registerAction(Request $request) {
        $nom = $request->query->get("nom");
        $prenom = $request->query->get("prenom");
        $password = $request->query->get("password");
        $email = $request->query->get("email");
        $role = $request->query->get("roles");
        $adress = $request->query->get("adress");

        $user = new User();
        $user->setPassword($password);
        $user->setEmail($email);
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setAdress($adress);
        $user->setRoles(array($role));

        try {

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return new Response("success");
        } catch (Exception $ex) {
            return new Response("fail");
        }
    }
    #[Route('/alluser', name: 'alluser', methods: ['GET'])]

    public function AllUsersAction()
    {
        $user = $this->getDoctrine()->getManager()->getRepository(User::class)->findAll();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($user);
        return new JsonResponse($formatted);
    }


}
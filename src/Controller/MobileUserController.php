<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface ;
use App\Entity\User;
use App\Form\UserRegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
//use App\Form\LoginType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
//use App\Form\UpdateType;
//use App\Entity\Urlizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
//use App\Repository\loginTimeRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/mobile')]
class MobileUserController extends AbstractController
{
    /**
     * @Route("/delete/{id}", name="deleteUser")
     * @Method("DELETE")
     * @param User $user
     * @return JsonResponse
     */
    function deleteUser(User $user): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize("User deleted");
        return new JsonResponse($formatted);
    }


    #[Route('/adduser', name: 'add_user', methods: 'POST')]
    public function addUser(Request $request,SerializerInterface $serializer,EntityManagerInterface $em): JsonResponse
    {
        $content = $request->getcontent();
        $post = $serializer->deserialize($content, User::class, 'json');

        $em->persist($post);
        $em->flush();


        return $this->json($post, 201, [], ['groups' => 'users']);
    }


//    public function adduser(Request $request, UserPasswordEncoderInterface $userPasswordEncoder): JsonResponse|Response
//    {
//        $user = new User();
//        $email = $request->query->get("email");
//        $password = $request->query->get("password");
//        $nom = $request->query->get("nom");
//        $role = $request->query->get("roles");
//        $prenom = $request->query->get("prenom");
//        $adress = $request->query->get("adress");
//        $tel = $request->query->get("tel");
//
//
//        $user = new User();
//        $user->setPassword($password);
//        $user->setEmail($email);
//        $user->setNom($nom);
//        $user->setPrenom($prenom);
//        $user->setAdress($adress);
//        $user->setRoles(array($role));
//        $roles[] = 'ROLE_USER';
//        try {
//
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($user);
//            $em->flush();
//            return new Response("success");
//        } catch (Exception $ex) {
//            return new Response("fail");
//        }
//    }

    /**
     * @Route("/loginn", name="login")
     * @param Request $request
     * @param  $email
     * @param  $password
     * @return Response
     */
    public function loginAction(Request $request, UserPasswordEncoderInterface $userPasswordEncoder){


   
        $user = new user();
        $email = $request->query->get("email");
        $password = $request->query->get("password");
        $hash_pass = md5($password);
 
        
        $Checkuser = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email, 'password' => $hash_pass]);
        
     
            $normalizer = new ObjectNormalizer ();
            $circularReferenceHandler = function ($Checkuser) {
                return $Checkuser -> getId ();
            };
            $serializer = new Serializer([ $normalizer ]);
            $formatted = $serializer->normalize($Checkuser , null , [ ObjectNormalizer::ATTRIBUTES => 
            ['id','email','roles','nom','prenom','image','tel','adress','password']]);
         
            return new JsonResponse(
                $formatted
         
            );


        
            


     
}

 /**
      * @Route("/updateUser", name="updateUser")
      * @Method("POST")
      * @return Response
      */
      public function updateUser(Request $request)
      {
          $user = new User();
          $id = $request->query->get("id");
          $email = $request->query->get("email");
          $nom = $request->query->get("nom");
          $prenom = $request->query->get("prenom");
          $adress = $request->query->get("adress");
          $password = $request->query->get("password");
          $image = $request->query->get("image");
          $tel = $request->query->get("tel");
          
          $rep = $this->getDoctrine()->getManager();
          $Checkuser = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);
  
          // Check if the user exists to prevent Integrity constraint violation error in the insertion
  
        
            $user = $rep->getRepository(User::class)->find($id);

          $user->setEmail($email);
          $user->setNom($nom);
          $user->setPrenom($prenom);
          $user->setAdress($adress);
          $user->setTel($tel);
          $user->setPassword($password);
          $user->setImage($image);

     
         
          $rep->flush();
          $serializer = new Serializer([new ObjectNormalizer()]);
          $formatted = $serializer->normalize("User mofifier");
          return new JsonResponse($formatted);
          
        }
          /**
     * @Route("/getUser", name="getUser")
     * @param Request $request
     * @return Response
     */
    public function getUserd(Request $request){


   
        $user = new user();
        $id = $request->query->get("id");

 
        
        $Checkuser = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $id]);
        
     
        $normalizer = new ObjectNormalizer ();
        $circularReferenceHandler = function ($Checkuser) {
            return $Checkuser -> getId ();
        };
            $serializer = new Serializer([ $normalizer ]);
            $formatted = $serializer->normalize($Checkuser , null , [ ObjectNormalizer::ATTRIBUTES => 
            ['id','email','roles','nom','prenom','image','tel','password']]);
         
            return new JsonResponse(
                $formatted
         
            );


        
            


     
}/**
      * @Route("/updateImg", name="updateimg")
      * @Method("POST")
      * @return Response
      */
      public function updateimg(Request $request)
      {
          $user = new User();
          $id = $request->query->get("id");
          $img = $request->query->get("Img");


          
          $rep = $this->getDoctrine()->getManager();

  
        
            $user = $rep->getRepository(User::class)->find($id);

          $user->setPhoto($img);


     
         
          $rep->flush();
          $serializer = new Serializer([new ObjectNormalizer()]);
          $formatted = $serializer->normalize("Image ajouter");
          return new JsonResponse($formatted);
          
        }
         /**
      * @Route("/updatepassword", name="updatepassword")
      * @Method("POST")
      * @return Response
      */
      public function updatepassword(Request $request)
      {
        $user = new User();
          $email = $request->query->get("email");
          $password = $request->query->get("password");


          
          $rep = $this->getDoctrine()->getManager();
       //   $Checkuser = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);
  
          // Check if the user exists to prevent Integrity constraint violation error in the insertion
  
        
          $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);

          $user->setPassword(md5($password));


     
         
          $rep->flush();
          $serializer = new Serializer([new ObjectNormalizer()]);
          $formatted = $serializer->normalize("Mot de passe a ete changer");
          return new JsonResponse($formatted);
          
        }
              /**
      * @Route("/checkemail", name="checkemail")
      * @Method("POST")
      * @return Response
      */
      public function checkemail(Request $request)
      {
        $user = new User();
          $email = $request->query->get("email");
   


          
          $rep = $this->getDoctrine()->getManager();
       //   $Checkuser = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);
  
          // Check if the user exists to prevent Integrity constraint violation error in the insertion
  
        
          $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);

    
if($user){

    $serializer = new Serializer([new ObjectNormalizer()]);
    $formatted = $serializer->normalize("email exist");
    return new JsonResponse($formatted);

}

      

          
        }
      }


    


<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(EntityManagerInterface $entityManager, ManagerRegistry $registry): Response
    {
        if (empty($_POST)) {
            return $this->render('login/index.html.twig', [
                'controller_name' => 'LoginController',
                'error' => 'Todos lo campos son obligatorios',
                'data' => $_POST
            ]);
        } else {
            try{
                $userRepository = new UserRepository($registry);
                $user = $userRepository->findOneByEmailAndPassword($_POST['email'], $_POST['password']);
                if ($user !== null) {
                    $msg = "Login realizado correctamente \nBienvenido: " . $user->getApellido2() . " " . $user->getApellido1() . ", " . $user->getNombre();
                    return $this->render('login/index.html.twig', [
                        'controller_name' => 'LoginController',
                        'continue' => $msg
                    ]);
                }
                else{
                    return $this->render('login/index.html.twig', [
                        'controller_name' => 'LoginController',
                        'error' => "El usuario o contraseña no es correcto"
                    ]);
                }
            }
            catch(Exception $ex){
                return $this->render('login/index.html.twig', [
                    'controller_name' => 'LoginController',
                    'error' => 'Se ha producido un error: ' . $ex->getMessage()
                ]);
            }
        }
    }
}

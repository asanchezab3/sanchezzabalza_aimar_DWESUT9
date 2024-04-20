<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;

class RegistroController extends AbstractController
{

    #[Route('/registro', name: 'app_registro')]
    public function index(): Response
    {
        return $this->render('registro/index.html.twig', [
            'controller_name' => 'RegistroController',
        ]);
    }

    #[Route('/registro/autoregistro', name: 'autoregistro')]
    public function add(EntityManagerInterface $entityManager): Response
    {
        if (empty($_POST)) {
            return $this->render('registro/index.html.twig', [
                'controller_name' => 'RegistroController',
                'error' => 'Todos lo campos son obligatorios',
                'data' => $_POST
            ]);
        } else {
            $user = new Users();
            $user->setApellido1($_POST['apellido1']);
            $user->setApellido2($_POST['apellido2']);
            $user->setNombre($_POST['nombre']);
            $user->setCorreo($_POST['email']);
            $user->setPass($_POST['password']);
            if (!$this->comprobarSeguridadContraseña($user->getPass())) {
                return $this->render('registro/index.html.twig', [
                    'controller_name' => 'RegistroController',
                    'error' => 'La seguridad de la contraseña no cumple los requsitos: Al menos 8 caracteres de longitud, Al menos una letra mayúscula, Al menos una letra minúscula y Al menos un número',
                    'data' => $_POST
                ]);
            }
            try {
                $usersDatabese = $entityManager->getRepository(Users::class)->findAll();
                // Iterar sobre los usuarios
                foreach ($usersDatabese as $userDatabase) {
                    if (strcasecmp($userDatabase->getCorreo(), $user->getCorreo()) === 0) {
                        return $this->render('login/index.html.twig', [
                            'controller_name' => 'LoginController',
                            'error' => 'El correo ya existía, úsalo con la contraseña que creaste en su día.'
                        ]);
                    }
                }


                $entityManager->persist($user);
                $entityManager->flush();
                return $this->render('login/index.html.twig', [
                    'controller_name' => 'LoginController',
                    'continue' => 'Usuario añadido correctamente.'
                ]);
            } catch (Exception $ex) {
                return $this->render('login/index.html.twig', [
                    'controller_name' => 'LoginController',
                    'error' => 'Se ha producido un error: ' . $ex->getMessage()
                ]);
            }
        }
    }

    function comprobarSeguridadContraseña($contrasena): bool
    {
        if (preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $contrasena)) {
            return true;
        }
        return false;
    }
}

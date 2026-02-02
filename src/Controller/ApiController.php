<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api', name: 'api_')]
final class ApiController extends AbstractController
{
    #[Route('/usuario', name: 'usuario_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        try {
            // Obtener datos JSON del request
            $data = json_decode($request->getContent(), true);

            // Validar que todos los campos requeridos estén presentes
            if (!isset($data['email']) || !isset($data['password']) || 
                !isset($data['latitud']) || !isset($data['longitud'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error en el registro del nuevo usuario',
                    'error' => 'Faltan campos requeridos'
                ], 400);
            }

            // Validar que el email no exista
            $usuarioExistente = $em->getRepository(Usuario::class)->findOneBy(['email' => $data['email']]);
            if ($usuarioExistente) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error en el registro del nuevo usuario',
                    'error' => 'La cuenta ya está dada de alta'
                ], 400);
            }

            // Crear nuevo usuario
            $usuario = new Usuario();
            $usuario->setEmail($data['email']);
            
            // Hashear la contraseña
            $hashedPassword = $passwordcHasher->hashPassword($usuario, $data['password']);
            $usuario->setPassword($hashedPassword);
            
            // Establecer coordenadas GPS
            $usuario->setLatitud((float)$data['latitud']);
            $usuario->setLongitud((float)$data['longitud']);
            
            // Generar token de acceso
            $token = bin2hex(random_bytes(32));
            $usuario->setToken($token);

            // Persistir el usuario
            $em->persist($usuario);
            $em->flush();

            // Respuesta exitosa
            return $this->json([
                'success' => true,
                'message' => 'Usuario registrado',
                'data' => [
                    'usuario_token' => $token
                ]
            ], 201);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error en el registro del nuevo usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        try {
            // Obtener datos JSON del request
            $data = json_decode($request->getContent(), true);

            // Validar que todos los campos requeridos estén presentes
            if (!isset($data['email']) || !isset($data['password'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Conexión no permitida',
                    'error' => 'Faltan campos requeridos'
                ], 400);
            }

            // Buscar usuario por email
            $usuario = $em->getRepository(Usuario::class)->findOneBy(['email' => $data['email']]);
            
            if (!$usuario) {
                return $this->json([
                    'success' => false,
                    'message' => 'Conexión no permitida',
                    'error' => 'Credenciales inválidas'
                ], 401);
            }

            // Verificar la contraseña
            if (!$passwordHasher->isPasswordValid($usuario, $data['password'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Conexión no permitida',
                    'error' => 'Credenciales inválidas'
                ], 401);
            }

            // Actualizar última conexión
            $usuario->setUltimaConexion(new \DateTimeImmutable());
            $em->flush();

            // Respuesta exitosa
            return $this->json([
                'success' => true,
                'message' => 'Usuario válido',
                'data' => [
                    'usuario_token' => $usuario->getToken()
                ]
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Conexión no permitida',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            // Obtener el token del header Authorization
            $authHeader = $request->headers->get('Authorization');
            
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al cerrar sesión',
                    'error' => 'Token no proporcionado'
                ], 401);
            }

            // Extraer el token
            $token = substr($authHeader, 7); // Quitar "Bearer "

            // Buscar usuario por token
            $usuario = $em->getRepository(Usuario::class)->findOneBy(['token' => $token]);
            
            if (!$usuario) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al cerrar sesión',
                    'error' => 'Token inexistente o ya expirado'
                ], 401);
            }

            // Invalidar el token generando uno nuevo
            $nuevoToken = bin2hex(random_bytes(32));
            $usuario->setToken($nuevoToken);
            $em->flush();

            // Respuesta exitosa
            return $this->json([
                'success' => true,
                'message' => 'Sesión cerrada correctamente'
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/home', name: 'home', methods: ['GET'])]
    public function home(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            // Obtener el token del header Authorization
            $authHeader = $request->headers->get('Authorization');
            
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->json([
                    'success' => false,
                    'message' => 'No hay usuarios cerca',
                    'error' => 'Token no proporcionado'
                ], 401);
            }

            // Extraer el token
            $token = substr($authHeader, 7); // Quitar "Bearer "

            // Buscar usuario por token
            $usuario = $em->getRepository(Usuario::class)->findOneBy(['token' => $token]);
            
            if (!$usuario) {
                return $this->json([
                    'success' => false,
                    'message' => 'No hay usuarios cerca',
                    'error' => 'Token inexistente o ya expirado'
                ], 401);
            }

            // Verificar que el usuario tenga coordenadas
            if (!$usuario->getLatitud() || !$usuario->getLongitud()) {
                return $this->json([
                    'success' => false,
                    'message' => 'No hay usuarios cerca',
                    'error' => 'Usuario sin coordenadas'
                ], 400);
            }

            // Buscar usuarios cercanos
            $usuariosCercanos = $em->getRepository(Usuario::class)
                ->buscarCercanos($usuario->getLatitud(), $usuario->getLongitud(), 5);

            // Filtrar el usuario actual de la lista
            $usuariosCercanos = array_filter($usuariosCercanos, function($u) use ($usuario) {
                return $u->getId() !== $usuario->getId();
            });

            // Si no hay usuarios cercanos
            if (empty($usuariosCercanos)) {
                return $this->json([
                    'success' => false,
                    'message' => 'No hay usuarios cerca',
                    'error' => 'No se encontraron registros en 5km'
                ], 404);
            }

            // Calcular distancias y formatear respuesta
            $usuariosFormateados = [];
            foreach ($usuariosCercanos as $usuarioCercano) {
                $distancia = $this->calcularDistancia(
                    $usuario->getLatitud(),
                    $usuario->getLongitud(),
                    $usuarioCercano->getLatitud(),
                    $usuarioCercano->getLongitud()
                );
                
                $usuariosFormateados[] = [
                    'distancia' => round($distancia, 1) . 'km'
                ];
            }

            // Respuesta exitosa
            return $this->json([
                'success' => true,
                'message' => 'Listado de usuarios cercanos',
                'data' => [
                    'usuarios_cercanos' => $usuariosFormateados
                ]
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'No hay usuarios cerca',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcula la distancia entre dos puntos usando la fórmula de Haversine
     */
    private function calcularDistancia(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $radioTierra = 6371; // Radio de la Tierra en km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $radioTierra * $c;
    }

    #[Route('/general', name: 'general', methods: ['GET'])]
    public function general(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            // Obtener el token del header Authorization
            $authHeader = $request->headers->get('Authorization');
            
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->json([
                    'success' => false,
                    'message' => 'Acceso no autorizado',
                    'error' => 'Token no proporcionado'
                ], 401);
            }

            // Extraer el token
            $token = substr($authHeader, 7); // Quitar "Bearer "

            // Buscar usuario por token
            $usuario = $em->getRepository(Usuario::class)->findOneBy(['token' => $token]);
            
            if (!$usuario) {
                return $this->json([
                    'success' => false,
                    'message' => 'Acceso no autorizado',
                    'error' => 'Token inexistente o ya expirado'
                ], 401);
            }

            // Obtener todos los mensajes del chat general (todos los mensajes públicos)
            // Ordenados por fecha de envío
            $mensajes = $em->createQueryBuilder()
                ->select('m')
                ->from('App\Entity\Mensaje', 'm')
                ->orderBy('m.fecha_envio', 'ASC')
                ->getQuery()
                ->getResult();

            // Formatear respuesta
            $mensajesFormateados = [];
            foreach ($mensajes as $mensaje) {
                $mensajesFormateados[] = [
                    'usuario' => $mensaje->getAutor()->getEmail(),
                    'texto' => $mensaje->getContenido(),
                    'hora' => $mensaje->getFechaEnvio()->format('H:i')
                ];
            }

            // Respuesta exitosa
            return $this->json([
                'success' => true,
                'message' => 'Mensajes del chat general',
                'data' => [
                    'mensajes' => $mensajesFormateados
                ]
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener mensajes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/privado', name: 'privado', methods: ['POST'])]
    public function privado(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            // Obtener datos JSON del request
            $data = json_decode($request->getContent(), true);

            // Validar campos requeridos
            if (!isset($data['token_usuario']) || !isset($data['nombre_sala'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al crear sala privada',
                    'error' => 'Faltan campos requeridos'
                ], 400);
            }

            // Buscar usuario por token
            $usuario = $em->getRepository(Usuario::class)->findOneBy(['token' => $data['token_usuario']]);
            
            if (!$usuario) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al crear sala privada',
                    'error' => 'Token inexistente o ya expirado'
                ], 401);
            }

            // Crear nuevo chat privado
            $chat = new Chat();
            $chat->setNombre($data['nombre_sala']);
            $chat->setTipo('privado');
            $chat->setCreador($usuario);
            
            // Generar ID único para la sala
            $idSala = strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
            
            // Persistir el chat
            $em->persist($chat);
            $em->flush();

            // Respuesta exitosa
            return $this->json([
                'success' => true,
                'message' => 'Sala privada creada',
                'data' => [
                    'id_sala' => $idSala,
                    'nombre' => $chat->getNombre()
                ]
            ], 201);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al crear sala privada',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/privado/cambiarchat', name: 'privado_cambiar_chat', methods: ['POST'])]
    public function cambiarChat(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            // Obtener datos JSON del request
            $data = json_decode($request->getContent(), true);

            // Validar campos requeridos
            if (!isset($data['token_usuario']) || !isset($data['id_sala_destino'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al cambiar de sala',
                    'error' => 'Faltan campos requeridos'
                ], 400);
            }

            // Buscar usuario por token
            $usuario = $em->getRepository(Usuario::class)->findOneBy(['token' => $data['token_usuario']]);
            
            if (!$usuario) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al cambiar de sala',
                    'error' => 'Token inexistente o ya expirado'
                ], 401);
            }

            // Aquí podrías validar que la sala existe y que el usuario tiene acceso
            // Por ahora solo confirmamos el cambio de sala

            // Respuesta exitosa
            return $this->json([
                'success' => true,
                'message' => 'Cambio de sala exitoso'
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al cambiar de sala',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/invitar', name: 'invitar', methods: ['POST'])]
    public function invitar(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            // Obtener datos JSON del request
            $data = json_decode($request->getContent(), true);

            // Validar campos requeridos
            if (!isset($data['token_usuario']) || !isset($data['id_usuario_invitado']) || !isset($data['id_sala'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al invitar',
                    'error' => 'Faltan campos requeridos'
                ], 400);
            }

            // Buscar usuario remitente por token
            $remitente = $em->getRepository(Usuario::class)->findOneBy(['token' => $data['token_usuario']]);
            
            if (!$remitente) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al invitar',
                    'error' => 'Token inexistente o ya expirado'
                ], 401);
            }

            // Buscar usuario invitado por ID
            $invitado = $em->getRepository(Usuario::class)->find($data['id_usuario_invitado']);
            
            if (!$invitado) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al invitar',
                    'error' => 'Usuario invitado no existe'
                ], 404);
            }

            // Verificar que ambos usuarios tengan coordenadas
            if (!$remitente->getLatitud() || !$remitente->getLongitud() || 
                !$invitado->getLatitud() || !$invitado->getLongitud()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al invitar',
                    'error' => 'Usuario sin coordenadas'
                ], 400);
            }

            // Verificar que el usuario invitado esté dentro del radio de 5km
            $distancia = $this->calcularDistancia(
                $remitente->getLatitud(),
                $remitente->getLongitud(),
                $invitado->getLatitud(),
                $invitado->getLongitud()
            );

            if ($distancia > 5) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al invitar',
                    'error' => 'El usuario ya no está en el radio de 5km'
                ], 400);
            }

            // Buscar sala por ID
            $sala = $em->getRepository(\App\Entity\Chat::class)->find($data['id_sala']);
            
            if (!$sala) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al invitar',
                    'error' => 'La sala no existe'
                ], 404);
            }

            // Verificar que la sala sea privada
            if ($sala->getTipo() !== 'privado') {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al invitar',
                    'error' => 'Solo se puede invitar a salas privadas'
                ], 400);
            }

            // Verificar que el remitente sea el creador de la sala
            if ($sala->getCreador()->getId() !== $remitente->getId()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al invitar',
                    'error' => 'Solo el creador puede invitar usuarios'
                ], 403);
            }

            // Verificar si ya existe una invitación pendiente
            $invitacionExistente = $em->getRepository(\App\Entity\Invitacion::class)->findOneBy([
                'remitente' => $remitente,
                'destinatario' => $invitado,
                'chat' => $sala
            ]);

            if ($invitacionExistente) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al invitar',
                    'error' => 'Ya existe una invitación para este usuario'
                ], 400);
            }

            // Crear la invitación
            $invitacion = new \App\Entity\Invitacion();
            $invitacion->setRemitente($remitente);
            $invitacion->setDestinatario($invitado);
            $invitacion->setChat($sala);

            $em->persist($invitacion);
            $em->flush();

            // Respuesta exitosa
            return $this->json([
                'success' => true,
                'message' => 'Invitación enviada correctamente'
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al invitar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

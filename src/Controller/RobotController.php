<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\Model;
use App\Entity\Robot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[Route('/api')]
class RobotController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function validateEntityById($entityClass, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entity = $entityManager->getRepository($entityClass)->findOneBy(['id' => $id]);

        if (!$entity) {
            return new JsonResponse(['error' => "Invalid $entityClass provided"], 400);
        }

        return $entity;
    }

    #[Route('/robot', name: 'app_robot', methods: ["GET"])]
    public function index(): JsonResponse
    {
        $robots = $this->getDoctrine()->getRepository(Robot::class)->findAll();

        $data = [];

        foreach ($robots as $robot) {
            $data[] = [
                'id' => $robot->getId(),
                'serial_number' => $robot->getSerialNumber(),
                'model' => $robot->getModel()->getName(),
                'employee' => $robot->getEmployee()->getName(),
                'production_date' => $robot->getProductionDate(),
                'status' => $robot->getStatus(),
                'created_at' => $robot->getCreatedAt(),
                'updated_at' => $robot->getUpdatedAt(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/robot/{id}', name: 'app_robot_show', methods: ["GET"])]
    public function show($id): JsonResponse
    {
        $robot = $this->getDoctrine()->getRepository(Robot::class)->find($id);

        $data = [];
        $data[] = [
            'id' => $robot->getId(),
            'serial_number' => $robot->getSerialNumber(),
            'model' => $robot->getModel()->getName(),
            'employee' => $robot->getEmployee()->getName(),
            'production_date' => $robot->getProductionDate(),
            'status' => $robot->getStatus(),
            'created_at' => $robot->getCreatedAt(),
            'updated_at' => $robot->getUpdatedAt(),
        ];

        return $this->json($data);
    }

    #[Route('/robot/store', name: 'app_robot_store', methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $robot = new Robot();

        $validator = Validation::createValidator();
        $violations = $validator->validate([
            'serial_number' => $request->request->get('serial_number'),
            'model' => $request->request->get('model'),
            'employee' => $request->request->get('employee'),
            'production' => $request->request->get('production')
        ], new Assert\Collection([
            'serial_number' => new Assert\NotBlank(),
            'employee' => new Assert\NotBlank(),
            'model' => new Assert\NotBlank(),
            'production' => new Assert\NotBlank()
        ]));

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return new JsonResponse(['errors' => $errors], 400);
        }
        $model_id = $this->validateEntityById(Model::class, $request->request->get('model'));
        $employee_id = $this->validateEntityById(Employee::class, $request->request->get('employee'));

        $production = new DateTime($request->request->get('production'));
        $robot->setSerialNumber($request->request->get('serial_number'));
        $robot->setModel($model_id);
        $robot->setStatus((int)$request->request->get('status'));
        $robot->setProductionDate($production);
        $robot->setEmployee(($employee_id));
        $robot->setCreatedAt(new \DateTime());
        $robot->setUpdatedAt(new \DateTime());

        $this->em->persist($robot);
        $this->em->flush();

        return new JsonResponse(['message' => 'Data stored successfully']);
    }

    #[Route('/robot/edit/{id}', name: 'app_robot_edit', methods: ["POST"])]
    public function update(Request $request, $id): JsonResponse
    {
        $robot = $this->em->getRepository(Robot::class)->find($id);

        if (!$robot) {
            return $this->json(['message' => "ID doesn't exist"]);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate([
            'serial_number' => $request->request->get('serial_number'),
            'model' => $request->request->get('model'),
            'employee' => $request->request->get('employee'),
            'production' => $request->request->get('production')
        ], new Assert\Collection([
            'serial_number' => new Assert\NotBlank(),
            'employee' => new Assert\NotBlank(),
            'model' => new Assert\NotBlank(),
            'production' => new Assert\NotBlank()
        ]));

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return new JsonResponse(['errors' => $errors], 400);
        }
        $model_id = $this->validateEntityById(Model::class, $request->request->get('model'));
        $employee_id = $this->validateEntityById(Employee::class, $request->request->get('employee'));

        $production = new DateTime($request->request->get('production'));
        $robot->setSerialNumber($request->request->get('serial_number'));
        $robot->setModel($model_id);
        $robot->setStatus((int)$request->request->get('status'));
        $robot->setProductionDate($production);
        $robot->setEmployee(($employee_id));
        $robot->setUpdatedAt(new \DateTime());

        $this->em->flush();

        return new JsonResponse(['message' => 'Data Update successfully']);
    }

    #[Route('/robot/destroy/{id}', name: 'app_robot_destroy', methods: ["DELETE"])]
    public function destroy($id): JsonResponse
    {
        $robot = $this->em->getRepository(Robot::class)->find($id);

        if (!$robot) {
            return $this->json(['message' => "ID doesn't exist"]);
        }

        $this->em->remove($robot);
        $this->em->flush();

        return $this->json(['message' => "destroy successfully"]);
    }
}

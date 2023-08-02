<?php

namespace App\Controller;

use App\Entity\Employee;
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
class EmployeeController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/employee', name: 'app_employee', methods: ["GET"])]
    public function index(): JsonResponse
    {
        $employees = $this->getDoctrine()->getRepository(Employee::class)->findAll();

        $data = [];

        foreach ($employees as $employee) {
            $data[] = [
                'id' => $employee->getId(),
                'name' => $employee->getName(),
                'email' => $employee->getEmail(),
                'position' => $employee->getPosition(),
                'hire_date' => $employee->getHireDate(),
                'CreatedAt' => $employee->getCreatedAt(),
                'UpdatedAt' => $employee->getUpdatedAt(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/employee/{id}', name: 'app_employee_show', methods: ["GET"])]
    public function show($id): JsonResponse
    {
        $employee = $this->getDoctrine()->getRepository(Employee::class)->find($id);

        if (!$employee) {
            return $this->json(['message' => "ID doesn't exist"]);
        }

        $date = [];

        $data = [
            'id' => $employee->getId(),
            'name' => $employee->getName(),
            'email' => $employee->getEmail(),
            'position' => $employee->getPosition(),
            'hire_date' => $employee->getHireDate(),
            'CreatedAt' => $employee->getCreatedAt(),
            'UpdatedAt' => $employee->getUpdatedAt()
        ];

        return $this->json($data);
    }

    #[Route('/employee/store', name: 'app_employee_sote', methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $employee = new Employee();

        $validator = Validation::createValidator();
        $violations = $validator->validate([
            'name' => $request->request->get('name'),
            'email' => $request->request->get('email'),
            'position' => $request->request->get('email'),
            'hire_date' => $request->request->get('hire_date')
        ], new Assert\Collection([
            'name' => new Assert\NotBlank(),
            'email' => new Assert\NotBlank(),
            'position' => new Assert\NotBlank(),
            'hire_date' => new Assert\NotBlank()
        ]));

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return new JsonResponse(['errors' => $errors], 400);
        }
        $hireDate = new DateTime($request->request->get('hire_date'));
        
        $employee->setName($request->request->get('name'));
        $employee->setEmail($request->request->get('email'));
        $employee->setPosition($request->request->get('email'));
        $employee->setHireDate($hireDate);
        $employee->setCreatedAt(new \DateTime());
        $employee->setUpdatedAt(new \DateTime());

        $this->em->persist($employee);
        $this->em->flush();
        return new JsonResponse(['message' => 'Data stored successfully']);
    }

    #[Route('/employee/edit/{id}', name: 'app_employee_edit', methods: ["POST"])]
    public function update(Request $request, $id): JsonResponse
    {
        $employee = $this->em->getRepository(Employee::class)->find($id);

        if (!$employee) {
            return $this->json(['message' => "ID doesn't exist"]);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate([
            'name' => $request->request->get('name'),
            'email' => $request->request->get('email'),
            'position' => $request->request->get('email'),
            'hire_date' => $request->request->get('hire_date')
        ], new Assert\Collection([
            'name' => new Assert\NotBlank(),
            'email' => new Assert\NotBlank(),
            'position' => new Assert\NotBlank(),
            'hire_date' => new Assert\NotBlank()
        ]));

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return new JsonResponse(['errors' => $errors], 400);
        }
        $hireDate = new DateTime($request->request->get('hire_date'));
        
        $employee->setName($request->request->get('name'));
        $employee->setEmail($request->request->get('email'));
        $employee->setPosition($request->request->get('email'));
        $employee->setHireDate($hireDate);
        $employee->setUpdatedAt(new \DateTime());

        $this->em->flush();
        return new JsonResponse(['message' => 'Data Update successfully']);
    }

    #[Route('/employee/destroy/{id}', name: 'app_employee_destroy', methods: ["DELETE"])]
    public function destroy($id): JsonResponse
    {
        $employee = $this->em->getRepository(Employee::class)->find($id);

        if (!$employee) {
            return $this->json(['message' => "ID doesn't exist"]);
        }

        $this->em->remove($employee);
        $this->em->flush();

        return $this->json(['message' => "destroy successfully"]);
    }
}

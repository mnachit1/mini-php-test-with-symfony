<?php

namespace App\Controller;

use App\Entity\Component;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api')]
class ComponentController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/component', name: 'app_component', methods: ["GET"])]
    public function index(): JsonResponse
    {
        $components = $this->getDoctrine()->getRepository(Component::class)->findAll();

        $date = [];

        foreach($components as $component)
        {
            $data[] = [
                'id' => $component->getId(),
                'name' => $component->getName(),
                'description' => $component->getDescription(),
                'cost' => $component->getCost(),
                'CreatedAt' => $component->getCreatedAt(),
                'UpdatedAt' => $component->getUpdatedAt(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/component/{id}', name: 'app_component_show', methods: ["GET"])]
    public function show($id): JsonResponse
    {
        $component = $this->getDoctrine()->getRepository(Component::class)->find($id);

        if (!$component) {
            return $this->json(['message' => "ID doesn't exist"]);
        }

        $date = [];

        $data = [
            'id' => $component->getId(),
            'name' => $component->getName(),
            'description' => $component->getDescription(),
            'cost' => $component->getCost(),
            'CreatedAt' => $component->getCreatedAt(),
            'UpdatedAt' => $component->getUpdatedAt(),
        ];

        return $this->json($data);
    }

    #[Route('/component/store', name: 'app_component_store', methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $component = new Component();

        $validator = Validation::createValidator();
        $violations = $validator->validate([
            'name' => $request->request->get('name'),
            'description' => $request->request->get('description'),
            'cost' => $request->request->get('cost')
        ], new Assert\Collection([
            'name' => new Assert\NotBlank(),
            'description' => new Assert\NotBlank(),
            'cost' => new Assert\NotBlank()
        ]));

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return new JsonResponse(['errors' => $errors], 400);
        }
        
        $cost = (int) $request->request->get('cost');
        $component->setName($request->request->get('name'));
        $component->setDescription($request->request->get('description'));
        $component->setCost($cost);
        $component->setCreatedAt(new \DateTime());
        $component->setUpdatedAt(new \DateTime());

        $this->em->persist($component);
        $this->em->flush();
        
        return new JsonResponse(['message' => 'Data stored successfully']);
    }

    #[Route('/component/edit/{id}', name: 'app_component_edit', methods: ["POST"])]
    public function update(Request $request ,$id): JsonResponse
    {
        $component = $this->em->getRepository(Component::class)->find($id);

        if (!$component) {
            return $this->json(['message' => "ID doesn't exist"]);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate([
            'name' => $request->request->get('name'),
            'description' => $request->request->get('description'),
            'cost' => $request->request->get('cost')
        ], new Assert\Collection([
            'name' => new Assert\NotBlank(),
            'description' => new Assert\NotBlank(),
            'cost' => new Assert\NotBlank()
        ]));

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return new JsonResponse(['errors' => $errors], 400);
        }
        $cost = (int) $request->request->get('cost');
        $component->setName($request->request->get('name'));
        $component->setDescription($request->request->get('description'));
        $component->setCost($cost);
        $component->setUpdatedAt(new \DateTime());

        $this->em->flush();
        
        return $this->json(array('message' => 'Data Update successfully'), 201);
    }

    #[Route('/component/destroy/{id}', name: 'app_component_destroy', methods: ["DELETE"])]
    public function destroy($id): JsonResponse
    {
        $component = $this->em->getRepository(Component::class)->find($id);

        if (!$component) {
            return $this->json(['message' => "ID doesn't exist"]);
        }

        $this->em->remove($component);
        $this->em->flush();

        return $this->json(['message' => "destroy successfully"]);
    }

}
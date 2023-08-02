<?php

namespace App\Controller;

use App\Entity\Model;
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
class ModelController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/model', name: 'app_model', methods: ["GET"])]
    public function index(): JsonResponse
    {
        $models = $this->getDoctrine()->getRepository(Model::class)->findAll();

        $data = [];

        foreach ($models as $model) {
            $data[] = [
                'id' => $model->getId(),
                'name' => $model->getName(),
                'manufacturer' => $model->getManufacturer(),
                'description' => $model->getDescription(),
                'cost' => $model->getCost(),
                'created_at' => $model->getCreatedAt(),
                'updated_at' => $model->getUpdatedAt(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/model/{id}', name: 'app_model_show', methods: ["GET"])]
    public function show($id): JsonResponse
    {
        $model = $this->getDoctrine()->getRepository(Model::class)->find($id);

        if (!$model) {
            return $this->json(['message' => "ID doesn't exist"]);
        }

        $data = [];

        $data = [
            'id' => $model->getId(),
            'name' => $model->getName(),
            'manufacturer' => $model->getManufacturer(),
            'description' => $model->getDescription(),
            'cost' => $model->getCost(),
            'created_at' => $model->getCreatedAt(),
            'updated_at' => $model->getUpdatedAt(),
        ];

        return $this->json($data);
    }

    #[Route('/model/store', name: 'app_model_store', methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $model = new Model();

        $validator = Validation::createValidator();
        $violations = $validator->validate([
            'name' => $request->request->get('name'),
            'manufacturer' => $request->request->get('manufacturer'),
            'description' => $request->request->get('description'),
            'cost' => $request->request->get('cost'),
        ], new Assert\Collection([
            'name' => new Assert\NotBlank(),
            'description' => new Assert\NotBlank(),
            'manufacturer' => new Assert\NotBlank(),
            'cost' => new Assert\NotBlank(),
        ]));

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return new JsonResponse(['errors' => $errors], 400);
        }

        $model->setName($request->request->get('name'));
        $model->setManufacturer($request->request->get('manufacturer'));
        $model->setDescription($request->request->get('description'));
        $model->setCost($request->request->get('cost'));
        $model->setCreatedAt(new \DateTime());
        $model->setUpdatedAt(new \DateTime());

        $this->em->persist($model);
        $this->em->flush();
        
        return new JsonResponse(['message' => 'Data stored successfully']);
    }

    #[Route('/model/edit/{id}', name: 'app_model_edit', methods: ["POST"])]
    public function update(Request $request,$id): JsonResponse
    {
        $model = $this->em->getRepository(Model::class)->find($id);

        if (!$model) {
            return $this->json(['message' => "ID doesn't exist"]);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate([
            'name' => $request->request->get('name'),
            'manufacturer' => $request->request->get('manufacturer'),
            'description' => $request->request->get('description'),
            'cost' => $request->request->get('cost'),
        ], new Assert\Collection([
            'name' => new Assert\NotBlank(),
            'description' => new Assert\NotBlank(),
            'manufacturer' => new Assert\NotBlank(),
            'cost' => new Assert\NotBlank(),
        ]));

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return new JsonResponse(['errors' => $errors], 400);
        }

        $model->setName($request->request->get('name'));
        $model->setManufacturer($request->request->get('manufacturer'));
        $model->setDescription($request->request->get('description'));
        $model->setCost($request->request->get('cost'));
        $model->setCreatedAt(new \DateTime());
        $model->setUpdatedAt(new \DateTime());

        $this->em->flush();
        
        return new JsonResponse(['message' => 'Data Update successfully']);
    }

    #[Route('/model/destroy/{id}', name: 'app_model_destroy', methods: ["DELETE"])]
    public function destroy($id): JsonResponse
    {
        $model = $this->em->getRepository(Model::class)->find($id);

        if (!$model) {
            return $this->json(['message' => "ID doesn't exist"]);
        }

        $this->em->remove($model);
        $this->em->flush();

        return $this->json(['message' => "destroy successfully"]);
    }
}

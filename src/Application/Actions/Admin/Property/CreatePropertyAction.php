<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Property;

use App\Application\Actions\HtmlAction;
use App\Application\Support\FlashMessage;
use App\Application\Support\PropertyUploadFailureHandler;
use App\Domain\Property\PropertyRepository;
use App\Domain\Property\PropertyValidator;
use App\Infrastructure\Upload\PropertyFileUploader;
use Psr\Http\Message\ResponseInterface as Response;

class CreatePropertyAction extends HtmlAction
{
    private PropertyRepository $propertyRepository;

    private PropertyValidator $validator;

    private PropertyFileUploader $fileUploader;

    private PropertyUploadFailureHandler $uploadFailureHandler;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Slim\Views\PhpRenderer $view,
        PropertyRepository $propertyRepository,
        PropertyValidator $validator,
        PropertyFileUploader $fileUploader,
        PropertyUploadFailureHandler $uploadFailureHandler
    ) {
        parent::__construct($logger, $view);
        $this->propertyRepository = $propertyRepository;
        $this->validator = $validator;
        $this->fileUploader = $fileUploader;
        $this->uploadFailureHandler = $uploadFailureHandler;
    }

    protected function action(): Response
    {
        $data = $this->getFormData();
        if (!is_array($data)) {
            $data = [];
        }

        $files = $this->normalizeFiles();
        $errors = $this->validator->validate($data, $files, true, null);

        if ($errors !== []) {
            return $this->render('admin/properties/form.php', [
                'property' => null,
                'errors' => $errors,
                'formData' => $data,
                'isEdit' => false,
            ]);
        }

        $userId = $this->getAuthUserId();
        $property = $this->validator->buildPropertyFromInput($data, null, $userId);
        $property = $this->propertyRepository->create($property);
        $propertyId = $property->getId();

        if ($propertyId === null) {
            FlashMessage::error('Failed to create property.');

            return $this->redirect('/admin/properties/create');
        }

        $failedStep = PropertyUploadFailureHandler::STEP_IMAGE;

        try {
            $imagePath = $this->fileUploader->upload($propertyId, $files['image'], 'image');

            $failedStep = PropertyUploadFailureHandler::STEP_THUMBNAIL;
            $thumbPath = $this->fileUploader->upload($propertyId, $files['thumbnail'], 'thumb');

            $property = $property->withImagePaths($imagePath, $thumbPath);

            $failedStep = PropertyUploadFailureHandler::STEP_SAVE;
            $this->propertyRepository->update($property);
        } catch (\Throwable $e) {
            $this->uploadFailureHandler->log($propertyId, $failedStep, $e);
            $this->propertyRepository->forceDelete($propertyId);
            $this->fileUploader->deletePropertyDirectory($propertyId);

            return $this->render('admin/properties/form.php', [
                'property' => null,
                'errors' => $this->uploadFailureHandler->fieldErrors($failedStep, $e),
                'formData' => $data,
                'isEdit' => false,
            ]);
        }

        FlashMessage::success('Property created successfully.');

        return $this->redirect('/admin/properties');
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function normalizeFiles(): array
    {
        return [
            'image' => $_FILES['image'] ?? null,
            'thumbnail' => $_FILES['thumbnail'] ?? null,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Property;

use App\Application\Actions\HtmlAction;
use App\Application\Support\FlashMessage;
use App\Domain\Property\PropertyRepository;
use App\Domain\Property\PropertyValidator;
use App\Infrastructure\Upload\PropertyFileUploader;
use Psr\Http\Message\ResponseInterface as Response;

class UpdatePropertyAction extends HtmlAction
{
    private PropertyRepository $propertyRepository;

    private PropertyValidator $validator;

    private PropertyFileUploader $fileUploader;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Slim\Views\PhpRenderer $view,
        PropertyRepository $propertyRepository,
        PropertyValidator $validator,
        PropertyFileUploader $fileUploader
    ) {
        parent::__construct($logger, $view);
        $this->propertyRepository = $propertyRepository;
        $this->validator = $validator;
        $this->fileUploader = $fileUploader;
    }

    protected function action(): Response
    {
        $id = (int) $this->resolveArg('id');
        $existing = $this->propertyRepository->findById($id);

        $data = $this->getFormData();
        if (!is_array($data)) {
            $data = [];
        }

        $files = $this->normalizeFiles();
        $errors = $this->validator->validate($data, $files, false, $existing);

        if ($errors !== []) {
            return $this->render('admin/properties/form.php', [
                'property' => $existing,
                'errors' => $errors,
                'formData' => $data,
                'isEdit' => true,
            ]);
        }

        $property = $this->validator->buildPropertyFromInput($data, $existing, $existing->getCreatedBy());

        $imageUploaded = isset($files['image']) && ($files['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
        $thumbUploaded = isset($files['thumbnail']) && ($files['thumbnail']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

        try {
            if ($imageUploaded) {
                $this->fileUploader->deleteByWebPath($existing->getImagePath());
                $imagePath = $this->fileUploader->upload($id, $files['image'], 'image');
                $property = $property->withImagePaths($imagePath, $property->getThumbnailPath());
            }
            if ($thumbUploaded) {
                $this->fileUploader->deleteByWebPath($existing->getThumbnailPath());
                $thumbPath = $this->fileUploader->upload($id, $files['thumbnail'], 'thumb');
                $property = $property->withImagePaths($property->getImagePath(), $thumbPath);
            }
        } catch (\Throwable $e) {
            return $this->render('admin/properties/form.php', [
                'property' => $existing,
                'errors' => ['image' => 'File upload failed. Please try again.'],
                'formData' => $data,
                'isEdit' => true,
            ]);
        }

        $this->propertyRepository->update($property);
        FlashMessage::success('Property updated successfully.');

        return $this->redirect('/admin/properties');
    }

    /**
     * @return array<string, array<string, mixed>|null>
     */
    private function normalizeFiles(): array
    {
        return [
            'image' => $_FILES['image'] ?? null,
            'thumbnail' => $_FILES['thumbnail'] ?? null,
        ];
    }
}

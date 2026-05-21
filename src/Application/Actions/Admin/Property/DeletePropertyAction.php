<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Property;

use App\Application\Actions\HtmlAction;
use App\Application\Support\FlashMessage;
use App\Domain\Property\PropertyRepository;
use App\Infrastructure\Upload\PropertyFileUploader;
use Psr\Http\Message\ResponseInterface as Response;

class DeletePropertyAction extends HtmlAction
{
    private PropertyRepository $propertyRepository;

    private PropertyFileUploader $fileUploader;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Slim\Views\PhpRenderer $view,
        PropertyRepository $propertyRepository,
        PropertyFileUploader $fileUploader
    ) {
        parent::__construct($logger, $view);
        $this->propertyRepository = $propertyRepository;
        $this->fileUploader = $fileUploader;
    }

    protected function action(): Response
    {
        $id = (int) $this->resolveArg('id');
        $this->propertyRepository->softDelete($id);
        $this->fileUploader->deletePropertyDirectory($id);
        FlashMessage::success('Property deleted successfully.');

        return $this->redirect('/admin/properties');
    }
}

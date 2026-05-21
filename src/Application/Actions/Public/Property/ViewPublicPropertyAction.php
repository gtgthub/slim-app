<?php

declare(strict_types=1);

namespace App\Application\Actions\Public\Property;

use App\Application\Actions\HtmlAction;
use App\Domain\Property\PropertyRepository;
use Psr\Http\Message\ResponseInterface as Response;

class ViewPublicPropertyAction extends HtmlAction
{
    private PropertyRepository $propertyRepository;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Slim\Views\PhpRenderer $view,
        PropertyRepository $propertyRepository
    ) {
        parent::__construct($logger, $view);
        $this->propertyRepository = $propertyRepository;
    }

    protected function action(): Response
    {
        $id = (int) $this->resolveArg('id');
        $property = $this->propertyRepository->findById($id);

        $backUrl = '/';
        $referer = $this->request->getHeaderLine('Referer');
        if ($referer !== '') {
            $parts = parse_url($referer);
            $path = $parts['path'] ?? '';
            if ($path === '/' || $path === '') {
                $backUrl = $referer;
            }
        }

        return $this->render('public/properties/detail.php', [
            'property' => $property,
            'backUrl' => $backUrl,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Actions\Public\Property;

use App\Application\Actions\HtmlAction;
use App\Application\Settings\SettingsInterface;
use App\Domain\Property\PropertyLocationSearchCriteria;
use App\Domain\Property\PropertyLocationSearchResult;
use App\Domain\Property\PropertyLocationSearchValidator;
use App\Domain\Property\PropertyRepository;
use Psr\Http\Message\ResponseInterface as Response;

class SearchPropertiesByLocationAction extends HtmlAction
{
    private PropertyRepository $propertyRepository;

    private PropertyLocationSearchValidator $locationSearchValidator;

    private SettingsInterface $settings;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Slim\Views\PhpRenderer $view,
        PropertyRepository $propertyRepository,
        PropertyLocationSearchValidator $locationSearchValidator,
        SettingsInterface $settings
    ) {
        parent::__construct($logger, $view);
        $this->propertyRepository = $propertyRepository;
        $this->locationSearchValidator = $locationSearchValidator;
        $this->settings = $settings;
    }

    protected function action(): Response
    {
        $queryParams = $this->request->getQueryParams();
        $formData = $this->locationSearchValidator->normalize($queryParams);
        $searchSubmitted = $this->locationSearchValidator->isSearchSubmitted($queryParams);

        $errors = [];
        $criteria = null;
        $results = [];
        $totalCount = 0;
        $resultsTruncated = false;

        if ($searchSubmitted) {
            $errors = $this->locationSearchValidator->validate($formData);
            if ($errors === []) {
                $criteria = PropertyLocationSearchCriteria::fromValidated($formData);
                $totalCount = $this->propertyRepository->countByLocationSearch($criteria);
                $resultsTruncated = $totalCount > PropertyLocationSearchCriteria::MAX_RESULTS;

                if ($totalCount > 0) {
                    $results = $this->propertyRepository->findByLocationSearch($criteria);
                }
            }
        }

        $googleMaps = $this->settings->get('google_maps');
        $mapMarkers = $this->buildMapMarkers($results);

        $searchLat = $criteria !== null
            ? $criteria->getLatitude()
            : (float) ($googleMaps['default_lat'] ?? 53.3498);
        $searchLng = $criteria !== null
            ? $criteria->getLongitude()
            : (float) ($googleMaps['default_lng'] ?? -6.2603);

        return $this->render('public/properties/location-search.php', [
            'formData' => $formData,
            'errors' => $errors,
            'searchSubmitted' => $searchSubmitted,
            'hasSearch' => $searchSubmitted && $errors === [],
            'results' => $results,
            'totalCount' => $totalCount,
            'resultsTruncated' => $resultsTruncated,
            'mapMarkers' => $mapMarkers,
            'googleMapsApiKey' => (string) ($googleMaps['api_key'] ?? ''),
            'searchLat' => $searchLat,
            'searchLng' => $searchLng,
            'radiusKm' => PropertyLocationSearchCriteria::RADIUS_KM,
            'activeTab' => (string) ($formData['tab'] ?? 'text'),
        ]);
    }

    /**
     * @param PropertyLocationSearchResult[] $results
     * @return list<array<string, int|float|string>>
     */
    private function buildMapMarkers(array $results): array
    {
        $markers = [];

        foreach ($results as $result) {
            $property = $result->getProperty();
            $lat = $property->getLatitude();
            $lng = $property->getLongitude();

            if ($lat === null || $lng === null) {
                continue;
            }

            $markers[] = [
                'id' => $property->getId(),
                'lat' => $lat,
                'lng' => $lng,
                'address' => $property->getDisplayableAddress(),
                'price' => number_format($property->getPrice(), 2),
                'listing' => $property->getListingTypeLabel(),
                'detailUrl' => '/properties/' . $property->getId(),
            ];
        }

        return $markers;
    }
}

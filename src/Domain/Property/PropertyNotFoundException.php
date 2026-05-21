<?php

declare(strict_types=1);

namespace App\Domain\Property;

use App\Domain\DomainException\DomainRecordNotFoundException;

class PropertyNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'Property not found.';
}

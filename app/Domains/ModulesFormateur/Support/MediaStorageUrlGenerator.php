<?php

namespace App\Domains\ModulesFormateur\Support;

use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class MediaStorageUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        return route('media.storage', ['path' => $this->getPathRelativeToRoot()]);
    }
}

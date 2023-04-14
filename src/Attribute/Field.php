<?php

namespace WebnetFr\DatabaseAnonymizerBundle\Attribute;

#[\Attribute]
class Field
{
    public function __construct(
        public string $generator,
        public string $formatter,
        public bool $preserveEmpty = true,
        public ?string $locale = null,
    ) {
    }
}

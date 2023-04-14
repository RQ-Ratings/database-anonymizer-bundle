<?php

namespace WebnetFr\DatabaseAnonymizerBundle\Attribute;

#[\Attribute]
class Field
{
    // @see \WebnetFr\DatabaseAnonymizer\GeneratorFactory\FakerGeneratorFactory for used options
    public function __construct(
        public string $generator,
        public string $formatter,
        public string $locale = 'en_GB',
        public bool $seed = false,
        public bool $unique = false,
        public mixed $optional = false,
        public array $arguments = [],
    ) {
    }
}

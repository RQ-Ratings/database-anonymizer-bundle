<?php

namespace WebnetFr\DatabaseAnonymizerBundle\Attribute;

#[\Attribute]
class Table
{
    public function __construct(
        public bool $truncate = false,
    ) {}
}

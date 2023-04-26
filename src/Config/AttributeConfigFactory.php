<?php

namespace WebnetFr\DatabaseAnonymizerBundle\Config;

use Doctrine\ORM\Mapping\ClassMetadata;
use WebnetFr\DatabaseAnonymizerBundle\Attribute\Field;
use WebnetFr\DatabaseAnonymizerBundle\Attribute\Table;

class AttributeConfigFactory
{
    /**
     * @param ClassMetadata[] $allMetadata
     *
     * @return array
     */
    public function getConfig(array $allMetadata)
    {
        $config = [];

        foreach ($allMetadata as $metadata) {
            $reflClass      = new \ReflectionClass($metadata->name);
            $classAttribute = $reflClass->getAttributes(Table::class);
            if (empty($classAttribute)) {
                continue;
            }

            $truncate = false;
            foreach($classAttribute as $attribute) {
                $arguments = $attribute->getArguments();
                if (isset($arguments['truncate'])) {
                    $truncate = $arguments['truncate'];
                }
            }

            $tableName          = $metadata->table['name'];
            $config[$tableName] = [
                'primary_key' => $metadata->identifier,
                'fields'      => [],
                'truncate'    => $truncate,
            ];

            foreach ($metadata->fieldMappings as $fieldName => $fieldMapping) {
                if (in_array($fieldName, $metadata->identifier)) {
                    continue;
                }

                $reflProperty       = $reflClass->getProperty($fieldName);
                $propertyAttributes = $reflProperty->getAttributes(Field::class);
                $fieldConfig        = null;

                if (!empty($propertyAttributes)) {
                    foreach ($propertyAttributes as $propertyAttribute) {
                        $fieldAttribute = $propertyAttribute->newInstance();
                        $fieldConfig    = [
                            'generator' => $fieldAttribute->generator,
                            'formatter' => $fieldAttribute->formatter,
                            'locale'    => $fieldAttribute->locale,
                            'seed'      => $fieldAttribute->seed,
                            'unique'    => $fieldAttribute->unique,
                            'optional'  => $fieldAttribute->optional,
                            'arguments' => $fieldAttribute->arguments,
                        ];
                        $config[$tableName]['fields'][$fieldMapping['columnName']] = $fieldConfig;
                        break;
                    }
                }
            }

            if (!$truncate && empty($config[$tableName]['fields'])) {
                unset($config[$tableName]);
            }
        }

        return ['tables' => $config];
    }
}

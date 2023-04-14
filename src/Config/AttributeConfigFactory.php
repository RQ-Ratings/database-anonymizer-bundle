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

            $tableName          = $metadata->table['name'];
            $config[$tableName] = [
                'primary_key' => $metadata->identifier,
                'fields'      => [],
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
                            'generator'      => $fieldAttribute->generator,
                            'formatter'      => $fieldAttribute->formatter,
                            'preserve_empty' => $fieldAttribute->preserveEmpty,
                            'locale'         => $fieldAttribute->locale,
                        ];
                        if ($fieldAttribute->locale === null) {
                            unset($fieldConfig['locale']);
                        }
                        $config[$tableName]['fields'][$fieldMapping['columnName']] = $fieldConfig;
                        break;
                    }
                }
            }

            if (empty($config[$tableName]['fields'])) {
                unset($config[$tableName]);
            }
        }

        return ['tables' => $config];
    }
}

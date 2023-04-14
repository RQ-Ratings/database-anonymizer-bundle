<?php

namespace WebnetFr\DatabaseAnonymizerBundle;

use Doctrine\DBAL\Connection;
use WebnetFr\DatabaseAnonymizer\Exception\InvalidAnonymousValueException;
use WebnetFr\DatabaseAnonymizer\TargetTable;

class CustomAnonymizer
{
    public const PDO_PGSQL = 'pdo_pgsql';

    /**
     * Anonymize entire database based on target tables.
     *
     * @param TargetTable[] $targets
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function anonymize(Connection $connection, array $targets)
    {
        foreach ($targets as $targetTable) {
            $allFieldNames = $targetTable->getAllFieldNames();
            $pk            = $targetTable->getPrimaryKey();

            // Select all rows form current table:
            // SELECT <all target fields> FROM <target table>
            $fetchRowsSQL = $connection->createQueryBuilder()
                ->select(implode(',', $allFieldNames))
                ->from($targetTable->getName())
                ->getSQL()
            ;
            $fetchRowsStmt = $connection->prepare($fetchRowsSQL);
            $result        = $fetchRowsStmt->execute();

            // Anonymize all rows in current target table.
            while ($row = $result->fetch()) {
                $values = [];
                // Anonymize all target fields in current row.
                foreach ($targetTable->getTargetFields() as $targetField) {
                    if (!empty($row[$targetField->getName()])) {
                        $anonValue = $targetField->generate();
                        if (null !== $anonValue && !\is_string($anonValue)) {
                            throw new InvalidAnonymousValueException('Generated value must be null or string');
                        }
                        // Set anonymized value.
                        $values[$targetField->getName()] = $anonValue;
                    }
                }

                $pkValues = [];
                foreach ($pk as $pkField) {
                    $pkValues[$pkField] = $row[$pkField];
                }

                if (!empty($values)) {
                    // Update current row with anonymized values.
                    $connection->update($targetTable->getName(), $values, $pkValues);
                }
            }
        }
    }
}

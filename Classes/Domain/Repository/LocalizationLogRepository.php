<?php

declare (strict_types = 1);

namespace NITSAN\NsT3AiLocalization\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

class LocalizationLogRepository  extends Repository
{
    private const TABLE_NAME = 'tx_nst3ai_domain_model_localizationlog';


    public function initializeObject(): void
    {
        /** @var Typo3QuerySettings $querySettings */
        $querySettings = $this->createQuery()->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    public function insertRecord(
        array $data
    ): void {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connectionPool
            ->getConnectionForTable(self::TABLE_NAME)
            ->insert(
                self::TABLE_NAME,
                $data
            );
    }

    public function findRecords(array $filter = [])
    {
        $constraints = [];
        $query = $this->createQuery();

        if(isset($filter['term']) && $filter['term']){
            $orconstraints[] = $query->like('extensionName', '%' . $filter['term'] . '%');
            $orconstraints[] = $query->like('content', '%' . $filter['term'] . '%');
            $orconstraints[] = $query->like('sourceFile', '%' . $filter['term'] . '%');
            $orconstraints[] = $query->like('outputFile', '%' . $filter['term'] . '%');
            $constraints[] = $query->logicalOr(...$orconstraints);
        }
        if(isset($filter['extensionKey']) && $filter['extensionKey']){
            $constraints[] = $query->equals('extensionName', $filter['extensionKey']);
        }

        if ($constraints) {
            $query->matching($query->logicalAnd(...$constraints));
        }
        $query->setOrderings(['uid' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute();

    }

}
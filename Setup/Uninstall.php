<?php

declare(strict_types=1);

namespace TNW\Marketing\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->revertConfig($setup);

        $setup->endSetup();
    }

    /**
     * Revert config changes during uninstallation.
     *
     * @param SchemaSetupInterface $setup
     */
    private function revertConfig(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $where = [
            'path IN (?)' => [
                'tnw_marketing/survey/start_date',
            ]
        ];
        $connection->delete($setup->getTable('core_config_data'), $where);
    }
}

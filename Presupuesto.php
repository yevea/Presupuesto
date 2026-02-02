<?php
/**
 * Presupuesto - Simple WooCommerce Sync Plugin
 */

namespace FacturaScripts\Plugins\Presupuesto;

use FacturaScripts\Core\Base\CronClass;
use FacturaScripts\Core\Base\PluginDeploy;
use FacturaScripts\Core\Tools;

class Presupuesto extends CronClass
{
    public function __construct()
    {
        $this->setPeriod(900); // 15 minutes
    }
    
    public function deploy(): void
    {
        // Install the page in database
        $installer = new PluginDeploy();
        $installer->deploy(__DIR__);
    }

    public function run(): void
    {
        if (Tools::settings('Presupuesto', 'enable_sync', false)) {
            Tools::log()->info('Presupuesto: Auto-sync running');
        }
    }
}

<?php

namespace Magmell\Contao\Autowrap\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Magmell\Contao\Autowrap\ContaoAutowrapBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ContaoAutowrapBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
        ];
    }
}

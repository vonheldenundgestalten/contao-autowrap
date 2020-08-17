<?php

// Palette append
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{autowrap_legend},autowrapElementTypes;';

// Fields
$GLOBALS['TL_DCA']['tl_settings']['fields']['autowrapElementTypes'] = array
(
    'inputType'               => 'select',
    'options_callback'        => array('tl_settings_autowrap', 'getContentElements'),
    'eval'                    => array('helpwizard'=>true, 'chosen'=>true, 'multiple'=>true, 'tl_class'=>'w50'),
);

/**
 * Class tl_settings_autowrap
 */
class tl_settings_autowrap
{

    /**
     * @param \Contao\DataContainer $dc
     * @return array
     */
    public function getContentElements($dc)
    {
        $groups = array();

        foreach ($GLOBALS['TL_CTE'] as $k=>$v)
        {
            foreach (array_keys($v) as $kk)
            {
                $groups[$k][] = $kk;
            }
        }

        return $groups;
    }
}

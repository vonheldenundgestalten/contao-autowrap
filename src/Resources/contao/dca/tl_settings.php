<?php

// Palette append
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{autowrap_legend},autowrapElementTypes;';

// Fields
$GLOBALS['TL_DCA']['tl_settings']['fields']['autowrapElementTypes'] = array
(
    'inputType'               => 'text',
    'eval'                    => array('tl_class'=>'w50')
);

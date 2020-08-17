<?php

// Hooks
$GLOBALS['TL_HOOKS']['getContentElement'][] = ['Magmell\Contao\Autowrap\Hooks\ContentElementsAutowrap', 'getContentElement'];
$GLOBALS['TL_HOOKS']['modifyFrontendPage'][] = ['Magmell\Contao\Autowrap\Hooks\ContentElementsAutowrap', 'modifyFrontendPage'];

<?php

namespace Magmell\Contao\Autowrap\Hooks;

use Contao\Config;
use Contao\System;
use Contao\ContentElement;
use Contao\ContentModel;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class ContentElementsAutowrap
{
    /** @var array */
    protected $arrElementTypesToAutoWrap;

    /** @var string */
    protected $wrapperStart = '<div class="autowrap autowrap-%s autowrap-element-count-#autowrap-element-count#"><div class="inside">';

    /** @var string */
    protected $wrapperEnd = '</div></div>';

    /** @var string */
    protected static $previousElementType;

    /** @var array */
    public static $elementsCount = [];

    /**
     * ContentElementsAutoWrapper constructor.
     */
    public function __construct()
    {
        $this->arrElementTypesToAutoWrap = unserialize(Config::get('autowrapElementTypes'));
    }

    /**
     * Wrap elements of same type that are configured in `tl_settings`.`autowrapElementTypes`
     *
     * @param ContentModel $objRow
     * @param string $strBuffer
     * @return string
     */
    public function getContentElement($objRow, $strBuffer)
    {
        if (!System::getContainer()->get('contao.routing.scope_matcher')->isFrontendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create('')))
        {
            return $strBuffer;
        }

        if (empty($this->arrElementTypesToAutoWrap)) 
        {
            return $strBuffer;
        }

        if ($objRow->type !== "alias" && in_array($objRow->type, $this->arrElementTypesToAutoWrap)) 
        {
            $blnWrapperStart = $this->wrapperStart($objRow);
            if ($blnWrapperStart)
            {
                $strBuffer = sprintf($this->wrapperStart, $objRow->type) . $strBuffer;
            }
            
            if ($this->wrapperEnd($objRow))
            {
                $strBuffer .= $this->wrapperEnd;
            }

            if ($blnWrapperStart)
            {
                static::$elementsCount[] = 1;
            } else {
                static::$elementsCount[count(static::$elementsCount) - 1] += 1;
            }
        }
        elseif($objRow->type == "alias") {

            // fetch the Alias element, and check if it's in the autowrap list, then start the logic
            $objAliasElement = ContentModel::findByPk($objRow->cteAlias);
	
            if (in_array($objAliasElement->type, $this->arrElementTypesToAutoWrap)) {

                $blnWrapperStart = $this->wrapperStart($objAliasElement);

                if ($blnWrapperStart) {
                    $strBuffer = sprintf($this->wrapperStart, $objAliasElement->type) . $strBuffer;
                }

                if ($this->wrapperEnd($objAliasElement)) {
                    $strBuffer .= $this->wrapperEnd;
                }

                if ($blnWrapperStart) {
                    static::$elementsCount[] = 1;
                } else {
                    static::$elementsCount[count(static::$elementsCount) - 1] += 1;
                }
            }
        }

        return $strBuffer;
    }

    /**
     * @param ContentModel $objRow
     * @return bool
     */
    protected function wrapperStart($objRow)
    {
        $objCte = ContentModel::findPublishedByPidAndTable($objRow->pid, $objRow->ptable);
        $arrCtes = $objCte->fetchAll();

        foreach ($arrCtes as $k => $arrCte)
        {
            // Current element
            if ($objRow->id == $arrCte['id'])
            {
                // It is first element or previous element is not of the same type
                if ($k === 0 || $arrCtes[$k - 1]['type'] !== $objRow->type)
                {
                    return true;
                }   
            }
        }

        return false;
    }

    /**
     * @param ContentElement $objRow
     * @return bool
     */
    protected function wrapperEnd($objRow)
    {
        $objCte = ContentModel::findPublishedByPidAndTable($objRow->pid, $objRow->ptable);
        $arrCtes = $objCte->fetchAll();

        foreach ($arrCtes as $k => $arrCte)
        {
            // Current element
            if ($objRow->id == $arrCte['id'])
            {
                // It is already last element or the next element is not of the same type
                if ($k === (count($arrCtes) - 1) || (array_key_exists($k + 1, $arrCtes) && $arrCtes[$k + 1]['type'] !== $objRow->type))
                {
                    return true;
                }   
            }
        }

        return false;
    }

    /**
     * @param string $strBuffer
     * @return string $strTemplateName
     * @return string
     */
    public function modifyFrontendPage($strBuffer, $strTemplateName)
    {
        if ('fe_page' !== substr($strTemplateName, 0, 7))
        {
            return $strBuffer;
        }

        // Replace placeholders with element counts
        foreach (static::$elementsCount as $count)
        {
            $needle = '#autowrap-element-count#';
            $pos = strpos($strBuffer, $needle);
            if ($pos !== false) {
                $strBuffer = substr_replace($strBuffer, $count, $pos, strlen($needle));
            }
        }

        return $strBuffer;
    }
}

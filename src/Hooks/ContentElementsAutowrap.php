<?php

namespace Magmell\Contao\Autowrap\Hooks;

use Contao\Config;
use Contao\ContentElement;
use Contao\ContentModel;

class ContentElementsAutowrap
{
    /** @var array */
    protected $arrElementTypesToAutoWrap;
    
    /** @var bool */
    protected static $currentlyWrapping;

    /** @var string */
    protected static $previousElementType;

    /** @var string */
    protected $wrapperStart = '<div class="autowrap autowrap-%s autowrap-element-count-#autowrap-element-count#">';

    /** @var string */
    public $wrapperEnd = '</div>';

    /** @var string */
    public $wrapperEndPossible = '<!-- POSSIBLE AUTO WRAPPER END -->';

    /** @var string */
    protected $bufferPre;

    /** @var string */
    protected $bufferPost;

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
     * @param ContentElement $objElement
     * @return string
     */
    public function getContentElement($objRow, $strBuffer, $objElement)
    {
        if (empty($this->arrElementTypesToAutoWrap))
        {
            return $strBuffer;
        }

        $this->bufferPre = '';
        $this->bufferPost = '';

        // Close previously opened wrapper
        if (static::$previousElementType && in_array(static::$previousElementType, $this->arrElementTypesToAutoWrap) && ($objElement->type !== static::$previousElementType))
        {
            $this->bufferPre = $this->wrapperEnd;
            static::$currentlyWrapping = false;
        }

        // Open wrapper
        if (!static::$currentlyWrapping && in_array($objElement->type, $this->arrElementTypesToAutoWrap))
        {
            $this->bufferPre .= sprintf($this->wrapperStart, $objElement->type);
            static::$currentlyWrapping = true;
        }

        if (in_array($objElement->type, $this->arrElementTypesToAutoWrap))
        {
            // Add always possible closing wrapper (as comment). Later we uncomment just the last one in the sequence, see method modifyFrontendPage()
            // We do this as we do not know if we have more elements in the sequence at this point
            // This method is invoked once per element and there is no clue if there is next element at all
            $this->bufferPost = $this->wrapperEndPossible;

            // Add to elements count
            $this->addCount($objElement->type);
        }


        static::$previousElementType = $objElement->type;

        return $this->bufferPre . $strBuffer . $this->bufferPost;
    }

    /**
     * @param string $currentElementType
     */
    protected function addCount($currentElementType)
    {
        if ($currentElementType === static::$previousElementType)
        {
            ++static::$elementsCount[count(static::$elementsCount) - 1];

        } else {
            static::$elementsCount[] = 1;
        }
    }

    public function modifyFrontendPage($strBuffer, $strTemplateName)
    {
        if ('fe_page' !== $strTemplateName)
        {
            return $strBuffer;
        }

        // Uncomment last possible auto wrapper end
        if (in_array(static::$previousElementType, $this->arrElementTypesToAutoWrap))
        {
            $strBuffer = $this->str_lreplace($this->wrapperEndPossible, $this->wrapperEnd, $strBuffer);
        }

        // Remove all possible wrapper end leftovers
        $strBuffer = str_replace($this->wrapperEndPossible, '', $strBuffer);

        // Replace placeholders with element count
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

    protected function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if ($pos !== false)
        {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }
}

<?php

/*
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\Request;

use Contao\StringUtil;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
use Wa72\HtmlPageDom\HtmlPageCrawler;

class Request
{
    /**
     * Object instance (Singleton).
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected static $objInstance;

    /**
     * Request object.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected static $request;

    /**
     * Prevent direct instantiation (Singleton).
     */
    protected function __construct()
    {
    }

    /**
     * Prevent cloning of the object (Singleton).
     */
    final public function __clone()
    {
    }

    /**
     * Return the object instance (Singleton).
     *
     * @return \Symfony\Component\HttpFoundation\Request The object instance
     */
    public static function getInstance()
    {
        if (null === static::$objInstance) {
            if (null === $_GET) {
                $_GET = [];
            }

            if (null === $_POST) {
                $_POST = [];
            }

            static::$objInstance = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        }

        // handle \Contao\Input unused $_GET parameters
        if (!empty($_GET)) {
            static::$objInstance->query->add($_GET);
        }

        // handle \Contao\Input unused $_POST parameters
        if (!empty($_POST)) {
            static::$objInstance->request->add($_POST);
        }

        return static::$objInstance;
    }

    /**
     * For test purposes use \Symfony\Component\HttpFoundation\Request::create() for dummy data.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public static function set(\Symfony\Component\HttpFoundation\Request $request)
    {
        static::$objInstance = $request;

        return static::$objInstance;
    }

    /**
     * Shorthand setter for query arguments ($_GET).
     *
     * @param string $strKey The requested field
     * @param mixed $varValue The input value
     */
    public static function setGet($strKey, $varValue)
    {
        // Convert special characters (see #7829)
        $strKey = str_replace([' ', '.', '['], '_', $strKey);

        $strKey = \Input::cleanKey($strKey);

        if (null === $varValue) {
            static::getInstance()->query->remove($strKey);
        } else {
            static::getInstance()->query->set($strKey, $varValue);
        }
    }

    /**
     * Shorthand setter for request arguments ($_POST).
     *
     * @param string $strKey The requested field
     * @param mixed $varValue The input value
     */
    public static function setPost($strKey, $varValue)
    {
        $strKey = \Input::cleanKey($strKey);

        if (null === $varValue) {
            static::getInstance()->request->remove($strKey);
        } else {
            static::getInstance()->request->set($strKey, $varValue);
        }
    }

    /**
     * Shorthand getter for query arguments ($_GET).
     *
     * @param string $strKey The requested field
     * @param bool $blnDecodeEntities If true, all entities will be decoded
     * @param bool $blnTidy If true, varValue is tidied up
     *
     * @return mixed If no $strkey is defined, return all cleaned query parameters, otherwise the cleaned requested query value
     */
    public static function getGet($strKey = null, $blnDecodeEntities = false, $blnTidy = false)
    {
        if (null === $strKey) {
            $arrValues = static::getInstance()->query;

            if ($blnDecodeEntities) {
                foreach ($arrValues as $key => &$varValue) {
                    $varValue = static::clean($varValue, $blnDecodeEntities, true, $blnTidy);
                }
            }

            return $arrValues;
        }

        return static::clean(static::getInstance()->query->get($strKey), $blnDecodeEntities, true, $blnTidy);
    }

    /**
     * XSS clean, decodeEntities, tidy/strip tags, encode special characters and encode inserttags and return save, cleaned value(s).
     *
     * @param mixed $varValue The input value
     * @param bool $blnDecodeEntities If true, all entities will be decoded
     * @param bool $blnEncodeInsertTags If true, encode the opening and closing delimiters of insert tags
     * @param bool $blnTidy If true, varValue is tidied up
     * @param bool $blnStrictMode If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return mixed The cleaned value
     */
    public static function clean($varValue, $blnDecodeEntities = false, $blnEncodeInsertTags = true, $blnTidy = true, $blnStrictMode = true)
    {
        // do not clean, otherwise empty string will be returned, not null
        if (null === $varValue) {
            return $varValue;
        }

        if (is_array($varValue)) {
            foreach ($varValue as $i => $childValue) {
                $varValue[$i] = static::clean($childValue, $blnDecodeEntities, $blnEncodeInsertTags, $blnTidy, $blnStrictMode);
            }

            return $varValue;
        }

        // do not handle binary uuid
        if (\Validator::isUuid($varValue)) {
            return $varValue;
        }

        $varValue = static::xssClean($varValue, $blnStrictMode);

        if ($blnTidy) {
            $varValue = static::tidy($varValue);
        } else {
            // decodeEntities for tidy is more complex, because non allowed tags should be displayed as readable text, not as html entity
            $varValue = \Input::decodeEntities($varValue);
        }

        // do not encodeSpecialChars when tidy did run, otherwise non allowed tags will be encoded twice
        if (!$blnDecodeEntities && !$blnTidy) {
            $varValue = \Input::encodeSpecialChars($varValue);
        }

        if ($blnEncodeInsertTags) {
            $varValue = \Input::encodeInsertTags($varValue);
        }

        return $varValue;
    }

    /**
     * XSS clean, decodeEntities, tidy/strip tags, encode special characters and encode inserttags and return save, cleaned value(s).
     *
     * @param mixed $varValue The input value
     * @param bool $blnDecodeEntities If true, all entities will be decoded
     * @param bool $blnEncodeInsertTags If true, encode the opening and closing delimiters of insert tags
     * @param string $strAllowedTags List of allowed html tags
     * @param bool $blnTidy If true, varValue is tidied up
     * @param bool $blnStrictMode If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return mixed The cleaned value
     */
    public static function cleanHtml($varValue, $blnDecodeEntities = false, $blnEncodeInsertTags = true, $strAllowedTags = null, $blnTidy = true, $blnStrictMode = true)
    {
        // do not clean, otherwise empty string will be returned, not null
        if (null === $varValue) {
            return $varValue;
        }

        if (is_array($varValue)) {
            foreach ($varValue as $i => $childValue) {
                $varValue[$i] = static::cleanHtml($childValue, $blnDecodeEntities, $blnEncodeInsertTags, $strAllowedTags, $blnTidy, $blnStrictMode);
            }

            return $varValue;
        }

        // do not handle binary uuid
        if (\Validator::isUuid($varValue)) {
            return $varValue;
        }

        $varValue = static::xssClean($varValue, $blnStrictMode);

        if ($blnTidy) {
            $varValue = static::tidy($varValue, $strAllowedTags, $blnDecodeEntities);
        } else {
            // decodeEntities for tidy is more complex, because non allowed tags should be displayed as readable text, not as html entity
            $varValue = \Input::decodeEntities($varValue);
        }

        // do not encodeSpecialChars when tidy did run, otherwise non allowed tags will be encoded twice
        if (!$blnDecodeEntities && !$blnTidy) {
            $varValue = \Input::encodeSpecialChars($varValue);
        }

        if ($blnEncodeInsertTags) {
            $varValue = \Input::encodeInsertTags($varValue);
        }

        return $varValue;
    }

    /**
     * XSS clean, preserve basic entities encode inserttags and return raw unsafe but filtered value.
     *
     * @param mixed $varValue The input value
     * @param bool $blnEncodeInsertTags If true, encode the opening and closing delimiters of insert tags
     * @param bool $blnTidy If true, varValue is tidied up
     * @param bool $blnStrictMode If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return mixed The cleaned value
     */
    public static function cleanRaw($varValue, $blnEncodeInsertTags = true, $blnTidy = false, $blnStrictMode = false)
    {
        // do not clean, otherwise empty string will be returned, not null
        if (null === $varValue) {
            return $varValue;
        }

        if (is_array($varValue)) {
            foreach ($varValue as $i => $childValue) {
                $varValue[$i] = static::cleanRaw($childValue, $blnEncodeInsertTags, $blnTidy, $blnStrictMode);
            }

            return $varValue;
        }

        // do not handle binary uuid
        if (\Validator::isUuid($varValue)) {
            return $varValue;
        }

        $varValue = static::xssClean($varValue, $blnStrictMode);

        if ($blnTidy) {
            $varValue = static::tidy($varValue);
        }

        $varValue = \Input::preserveBasicEntities($varValue);

        if ($blnEncodeInsertTags) {
            $varValue = \Input::encodeInsertTags($varValue);
        }

        return $varValue;
    }

    /**
     * Returns true if the get parameter is defined.
     *
     * @param string $strKey The key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public static function hasGet($strKey)
    {
        return static::getInstance()->query->has($strKey);
    }

    /**
     * Shorthand getter for request arguments ($_POST).
     *
     * @param string $strKey The requested field
     * @param bool $blnDecodeEntities If true, all entities will be decoded
     * @param bool $blnTidy If true, varValue is tidied up
     * @param bool $blnStrictMode If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return mixed If no $strKey is defined, return all cleaned query parameters, otherwise the cleaned requested query value
     */
    public static function getPost($strKey = null, $blnDecodeEntities = false, $blnTidy = true, $blnStrictMode = true)
    {
        if (null === $strKey) {
            $arrValues = static::getInstance()->request;

            if (is_array($arrValues)) {
                foreach ($arrValues as $key => &$varValue) {
                    $varValue = static::clean($varValue, $blnDecodeEntities, TL_MODE !== 'BE', $blnTidy, $blnStrictMode);
                }
            }

            return $arrValues;
        }

        return static::clean(static::getInstance()->request->get($strKey), $blnDecodeEntities, TL_MODE !== 'BE', $blnTidy, $blnStrictMode);
    }

    /**
     * Shorthand getter for request arguments ($_POST) preserving allowed HTML tags.
     *
     * @param string $strKey The requested field
     * @param bool $blnDecodeEntities If true, all entities will be decoded
     * @param string $strAllowedTags List of allowed html tags
     * @param bool $blnTidy If true, varValue is tidied up
     * @param bool $blnStrictMode If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return mixed If no $strKey is defined, return all cleaned query parameters, otherwise the cleaned requested query value
     */
    public static function getPostHtml($strKey = null, $blnDecodeEntities = false, $strAllowedTags = null, $blnTidy = true, $blnStrictMode = true)
    {
        if (null === $strKey) {
            $arrValues = static::getInstance()->request;

            if (is_array($arrValues)) {
                foreach ($arrValues as $key => &$varValue) {
                    $varValue = static::cleanHtml($varValue, $blnDecodeEntities, TL_MODE !== 'BE', $strAllowedTags, $blnTidy, $blnStrictMode);
                }
            }

            return $arrValues;
        }

        return static::cleanHtml(static::getInstance()->request->get($strKey), $blnDecodeEntities, TL_MODE !== 'BE', $strAllowedTags, $blnTidy, $blnStrictMode);
    }

    /**
     * Shorthand getter for request arguments ($_POST), returning raw, unsafe but filtered values.
     *
     * @param string $strKey The requested field
     * @param bool $blnTidy If true, varValue is tidied up
     * @param bool $blnStrictMode If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return mixed If no $strkey is defined, return all cleaned query parameters, otherwise the cleaned requested query value
     */
    public static function getPostRaw($strKey = null, $blnTidy = false, $blnStrictMode = false)
    {
        if (null === $strKey) {
            $arrValues = static::getInstance()->request;

            if (is_array($arrValues)) {
                foreach ($arrValues as $key => &$varValue) {
                    $varValue = static::cleanRaw($varValue, TL_MODE !== 'BE', $blnTidy, $blnStrictMode);
                }
            }

            return $arrValues;
        }

        return static::cleanRaw(static::getInstance()->request->get($strKey), TL_MODE !== 'BE', $blnTidy, $blnStrictMode);
    }

    /**
     * Clean a value and try to prevent XSS attacks.
     *
     * @param mixed $varValue A string or array
     * @param bool $blnStrictMode If true, the function removes also JavaScript event handlers
     * @param bool $blnTidy If true, varValue is tidied up
     *
     * @return mixed The cleaned string or array
     */
    public static function xssClean($varValue, $blnStrictMode = false)
    {
        if (is_array($varValue)) {
            foreach ($varValue as $key => $value) {
                $varValue[$key] = static::xssClean($value, $blnStrictMode);
            }

            return $varValue;
        }

        // do not xss clean binary uuids
        if (\Validator::isBinaryUuid($varValue)) {
            return $varValue;
        }

        $varValue = \StringUtil::decodeEntities($varValue);

        $varValue = preg_replace('/(&#[A-Za-z0-9]+);?/i', '$1;', $varValue);

        // fix: "><script>alert('xss')</script> or '></SCRIPT>">'><SCRIPT>alert(String.fromCharCode(88,83,83))</SCRIPT>
        $varValue = preg_replace('/(?<!\w)(?>["|\']>)+(<[^\/^>]+>.*)/', '$1', $varValue);

        $varValue = \Input::xssClean($varValue, $blnStrictMode);

        return $varValue;
    }

    /**
     * Tidy an value.
     *
     * @param string $varValue Input value
     * @param string $strAllowedTags Allowed tags as string `<p><span>`
     * @param bool $blnDecodeEntities If true, all entities will be decoded
     *
     * @return string The tidied string
     */
    public static function tidy($varValue, $strAllowedTags = '', $blnDecodeEntities = false)
    {
        if (!$varValue) {
            return $varValue;
        }

        // do not tidy non-xss critical characters for performance
        if(!preg_match('#"|\'|<|>|\(|\)#', \StringUtil::decodeEntities($varValue)))
        {
            return $varValue;
        }

        // remove illegal white spaces after closing tag slash <br / >
        $varValue = preg_replace('@\/(\s+)>@', '/>', $varValue);

        // Encode opening tag arrow brackets
        $varValue = preg_replace_callback('/<(?(?=!--)!--[\s\S]*--|(?(?=\?)\?[\s\S]*\?|(?(?=\/)\/[^.\-\d][^\/\]\'"[!#$%&()*+,;<=>?@^`{|}~ ]*|[^.\-\d][^\/\]\'"[!#$%&()*+,;<=>?@^`{|}~ ]*(?:\s[^.\-\d][^\/\]\'"[!#$%&()*+,;<=>?@^`{|}~ ]*(?:=(?:"[^"]*"|\'[^\']*\'|[^\'"<\s]*))?)*)\s?\/?))>/', function ($matches) {
            return substr_replace($matches[0], '&lt;', 0, 1);
        }, $varValue);

        // Encode less than signs that are no tags with [lt]
        $varValue = str_replace('<', '[lt]', $varValue);

        // After we saved less than signs with [lt] revert &lt; sign to <
        $varValue = \StringUtil::decodeEntities($varValue);

        // Restore HTML comments
        $varValue = str_replace(['&lt;!--', '&lt;!['], ['<!--', '<!['], $varValue);

        // Recheck for encoded null bytes
        while (false !== strpos($varValue, '\\0')) {
            $varValue = str_replace('\\0', '', $varValue);
        }

        $objCrawler = new HtmlPageCrawler($varValue);

        if (!$objCrawler->isHtmlDocument()) {
            $objCrawler = new HtmlPageCrawler('<div id="tidyWrapperx123x123xawec3">' . $varValue . '</div>');
        }

        $arrAllowedTags = explode('<', str_replace('>', '', $strAllowedTags));
        $arrAllowedTags = array_filter($arrAllowedTags);

        try {
            if (!empty($arrAllowedTags)) {
                $objCrawler->filter('*')->each(function ($node, $i) use ($arrAllowedTags) {
                    /** @var $node HtmlPageCrawler */

                    // skip wrapper
                    if ('tidyWrapperx123x123xawec3' === $node->getAttribute('id')) {
                        return $node;
                    }

                    if (!in_array($node->getNode(0)->tagName, $arrAllowedTags, true)) {
                        $strHTML = $node->saveHTML();
                        $strHTML = str_replace(['<', '>'], ['[[xlt]]', '[[xgt]]'], $strHTML);

                        // remove unwanted tags and return the element text
                        return $node->replaceWith($strHTML);
                    }

                    return $node;
                });
            }
            // unwrap div#tidyWrapper and set value to its innerHTML
            if (!$objCrawler->isHtmlDocument()) {
                $varValue = $objCrawler->filter('div#tidyWrapperx123x123xawec3')->getInnerHtml();
            } else {
                $varValue = $objCrawler->saveHTML();
            }

            // HTML documents or fragments, Crawler first converts all non-ASCII characters to entities (see: https://github.com/wasinger/htmlpagedom/issues/5)
            $varValue = StringUtil::decodeEntities($varValue);

            // trim last [nbsp] occurance
            $varValue = preg_replace('@(\[nbsp\])+@', '', $varValue);
        } catch (SyntaxErrorException $e) {
        }

        $varValue = static::restoreBasicEntities($varValue, $blnDecodeEntities);

        if (!$blnDecodeEntities) {
            $varValue = \Input::encodeSpecialChars($varValue);
        }

        // encode unwanted tag opening and closing brakets
        $arrSearch  = ['[[xlt]]', '[[xgt]]'];
        $arrReplace = ['&#60;', '&#62;'];
        $varValue   = str_replace($arrSearch, $arrReplace, $varValue);

        return $varValue;
    }

    /**
     * Restore basic entities.
     *
     * @param string $strBuffer The string with the tags to be replaced
     * @param bool $blnDecodeEntities If true, all entities will be decoded
     *
     * @return string The string with the original entities
     */
    public static function restoreBasicEntities($strBuffer, $blnDecodeEntities = false)
    {
        $strBuffer = str_replace(['[&]', '[&amp;]', '[lt]', '[gt]', '[nbsp]', '[-]'], ['&amp;', '&amp;', '&lt;', '&gt;', '&nbsp;', '&shy;'], $strBuffer);

        if ($blnDecodeEntities) {
            $strBuffer = \StringUtil::decodeEntities($strBuffer);
        }

        return $strBuffer;
    }

    /**
     * Returns true if the post parameter is defined.
     *
     * @param string $strKey The key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public static function hasPost($strKey)
    {
        return static::getInstance()->request->has($strKey);
    }
}

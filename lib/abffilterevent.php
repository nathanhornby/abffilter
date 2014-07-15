<?php

/**
 * This File is part of package name
 *
 * (c) author <email>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

require_once(TOOLKIT . '/class.event.php');

/**
 * Class: ABFFilterEvent
 *
 * @see SectionEvent
 * @abstract
 */
abstract class ABFFilterEvent extends SectionEvent
{
    public $ROOTELEMENT;

    public $eParamFILTERS = array('xss-fail');
    public $abffName;

    public function priority()
    {
        return self::kHIGH;
    }

    public static function allowEditorToParse()
    {
        return false;
    }

    protected function __trigger()
    {
        $result = new XMLElement($this->ROOTELEMENT);
        $entry_id = null;
        $fields = isset($_REQUEST['fields']) ? $_REQUEST['fields'] : array();
        $post_values = new XMLElement('post-values');

        if (is_array($fields) && !empty($fields)) {
            General::array_to_xml($post_values, $fields, true);
        }

        if ($this->processPreSaveFilters($result, $fields, $post_values, $entry_id) === false) {
            return false;
        }

        $driver = Symphony::ExtensionManager()->getInstance('abffilter');
        $driver->getABFDriver();
        require_once dirname(dirname(__FILE__)) . '/lib/abffilter.php';

        $filter = new ABFFilter(ABF::instance(), Symphony::Database());
        $this->executeABFFilter($filter, $result);

        $filterElement = SectionEvent::buildFilterElement($this->abffName, $filter->getStatus(), $filter->getMessage());
        $result->appendChild($filterElement);

        $result->appendChild($post_values);
        return $result;
    }

    /**
     * __doit
     *
     * @param mixed $result
     */
    abstract protected function executeABFFilter(ABFFilter $filter, XMLElement &$result);
}

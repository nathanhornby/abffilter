<?php

/**
 * This File is part of package name
 *
 * (c) author <email>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */
require_once dirname(dirname(__FILE__)) . '/lib/abffilterevent.php';

/**
 * Class: eventABFFilterUnban
 *
 * @see ABFFilterEvent
 */
class eventABFFilterUnban extends ABFFilterEvent
{

    public $ROOTELEMENT = 'ubhash';

    public $abffName = 'unban-with-hash';

    public static function about()
    {
        return array(
            'name' => 'ABFFilter: unban IP',
            'author' => array(
                'name' => 'Thomas Appel',
                'website' => 'http://thomas-appel.com',
                'email' => 'mail@thomas-appel.com'),
            'version' => 'ABFFilter 1.0',
            'release-date' => '2012-02-27'
        );
    }

    public function load()
    {
        if (isset($this->_env['param'][$this->ROOTELEMENT]) && !empty($this->_env['param'][$this->ROOTELEMENT])) {
            return $this->__trigger();
        }
    }


    /**
     * executeABFFilter
     *
     * @param ABFFilter $filter
     * @param XMLElement $result
     */
    protected function executeABFFilter(ABFFilter $filter, XMLElement &$result)
    {
        if (!$filter->unbanWithHash($this->_env['param'][$this->ROOTELEMENT])) {
            $result->setAttribute('result', 'error');
        } else {
            $result->setAttribute('result', 'success');
        }
    }
}

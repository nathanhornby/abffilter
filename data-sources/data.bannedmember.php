<?php

/**
 * This File is part of package name
 *
 * (c) author <email>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */
require_once(TOOLKIT . '/class.datasource.php');

class DatasourceBannedMember extends Datasource
{
    /**
     * driver
     *
     * @var mixed
     */
    protected $driver;

    public $dsParamROOTELEMENT = 'abffilter';
    /**
     * __construct
     *
     */
    public function __construct()
    {
        $this->driver = Symphony::ExtensionManager()->getInstance('abffilter');
    }

    /**
     * allowEditorToParse
     *
     */
    public function allowEditorToParse()
    {
        return true;
    }

    public function about()
    {
        return array(
            'name' => 'ABFFilter: Ban status',
            'author' => array(
                'name' => 'Thomas Appel',
                'email' => 'mail@thomas-appel.com'),
            'version' => 'Symphony 2.3.2beta2',
            'release-date' => '2013-02-22T11:28:00+00:00'
        );
    }

    public function execute(array &$param_pool = null)
    {
        $this->driver->getABFDriver();
        $param_pool['is-currently-banned'] = ABF::instance()->isCurrentlyBanned() ? 'yes' : 'no';
        $param_pool['is-blacklisted'] = ABF::instance()->isBlackListed() ? 'yes' : 'no';
        return null;
    }
}

<?php

/**
 * This file is part of the ABFFilter extension for Symphony CMS
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */
require_once EXTENSIONS . '/anti_brute_force/extension.driver.php';

/**
 * Class: Extension_ABFFilter
 *
 * @see Extension
 * @license MIT
 */
class Extension_ABFFilter extends Extension
{
    /**
     * ABFDriver
     *
     * @var Extension_Anti_Brute_Force
     */
    protected $ABFDriver;

    /**
     * MembersDriver
     *
     * @var Extension_Members
     */
    protected $MembersDriver;

    /**
     * getSubscribedDelegates
     *
     */
    public function getSubscribedDelegates()
    {
        return array(
            array(
                'page' => '/frontend/',
                'delegate' => 'MembersLoginFailure',
                'callback' => 'membersLoginFailure'
            ),
            array(
                'page' => '/frontend/',
                'delegate' => 'MembersLoginSuccess',
                'callback' => 'membersLoginSuccess'
            ),
            array(
                'page' => '/system/preferences/',
                'delegate' => 'AddCustomPreferenceFieldsets',
                'callback' => 'appendPreferences'
            ),
            array(
                'page' => '/system/preferences/',
                'delegate' => 'Save',
                'callback' => 'savePreferences'
            ),
            array(
                'page' => '/frontend/',
                'delegate' => 'FrontendEventPostProcess',
                'callback' => 'checkIPBanFromEventNode'
            )
        );
    }

    /**
     * Traverse the event nodelist end determine if a password-reset event has
     * failed due to false authentication.
     *
     * @param mixed $context
     */
    public function checkIPBanFromEventNode($context)
    {
        $eventNode = $context['xml'];

        if ($filterNodeList = $eventNode->getChildrenByName('members-reset-password')) {
            $filterNode = current($filterNodeList);

            $driver = $this->getMembersDriver();
            $auth = $driver->getMemberDriver()->section->getField('authentication');
            $id = $auth->get('element_name');

            if ($filterNode->getAttribute('result') === 'error') {

                if ($pwNodeList = $filterNode->getChildrenByName($id)) {

                    $pwNode = current($pwNodeList);

                    if ($pwNode->getAttribute('type') === 'invalid') {

                        $fields = $_REQUEST['fields'];
                        $identity = $driver->getMemberDriver()->setIdentityField($fields, false);
                        $username = $identity && $identity->get('type') === 'memberusername' ?  $fields[$identity->get('element_name')] : null;

                        return $this->membersLoginFailure(array('username' => $username));
                    }
                }
            } else if ($filterNode->getAttribute('result') === 'success') {
                return $this->membersLoginSuccess(array());
            }
        }
        return;
    }


    /**
     * membersLoginSuccess
     *
     * @param mixed $context
     */
    public function membersLoginSuccess($context) {
        // do not do anything if ip is white listed
        if (ABF::instance()->isCurrentlyBanned()) {
            $this->getMembersDriver()->getMemberDriver()->logout();
            redirect($_SERVER['HTTP_REFERER']); exit(1);
        } else {
            // unregister any result with current IP
            ABF::instance()->unregisterFailure();
        }
    }

    /**
     * membersLoginFailure
     *
     * @param mixed $context
     */
    public function membersLoginFailure($context) {
        return $this->getABFDriver()->authorLoginFailure($context);
    }

    /**
     * getABFDriver
     *
     */
    public function getABFDriver()
    {
        if (!($this->ABFDriver instanceof extension_anti_brute_force)) {
            $this->ABFDriver = Symphony::ExtensionManager()->getInstance('anti_brute_force');
        }

        return $this->ABFDriver;
    }

    /**
     * getMembersDriver
     *
     */
    public function getMembersDriver()
    {
        if (!($this->MembersDriver instanceof extension_members)) {
            $this->MembersDriver = Symphony::ExtensionManager()->getInstance('members');
        }

        return $this->MembersDriver;
    }

    /**
     * append preference panel
     *
     * @param array $context
     */
    public function appendPreferences($context)
    {
        extract($context);

        $fieldset = new XMLElement('fieldset', null, array(
            'class' => 'settings',
            'id' => $this->name
        ));

        $legend = new XMLElement('legend', 'ABF Filter');
        $fieldset->appendChild($legend);

        $div = new XMLElement('div', null, array(
            'class' => 'contents'
        ));
        $field = Widget::Input('settings[abffilter][unban-url]', Symphony::Configuration()->get('unban-url', 'abffilter'), 'text');
        $label = Widget::Label(__('Unban URL'), $field);
        $help = new XMLElement('p', 'Use this url as base url for unbanning IP blocks via e-mail. Ensure that this page exists and has a url parameter named `$ubhash`', array('class' => 'help'));
        $label->appendChild($help);
        $div->appendChild($label);
        $fieldset->appendChild($div);
        $wrapper->appendChild($fieldset);
    }

    /**
     * savePreferences
     *
     * @param array $context
     * @param bool $override
     *
     */
    public function savePreferences($context, $override = false)
    {
        $val = $context['settings']['abffilter']['unban-url'];
        Symphony::Configuration()->set('unban-url', urlencode(trim($val, '/') . '/'), 'abffilter');
        Symphony::Configuration()->write();
    }
}

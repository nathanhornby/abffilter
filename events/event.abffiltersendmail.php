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
 * Class: eventABFFilterSendMail
 *
 * @see ABFFilterEvent
 */
class eventABFFilterSendMail extends ABFFilterEvent
{

    public $ROOTELEMENT = 'abffilter-sendmail';
    public $abffName = 'sendmail';

    public static function documentation()
    {
        $dsc = '<h3>Example Front-end Form Markup</h3><p>For the event to work it is nessesary to provide a field named <code>email</code>. The event itself is triggered with <code>abffilter-action[sendmail]</code></p>';
        $form =
            '
        <form method="post" action="" enctype="multipart/form-data">
            <input name="MAX_FILE_SIZE" type="hidden" value="5242880" />
            <label>Your IP is currently banned. Try to unban your IP with your email address.
                <input name="email" type="text"/>
            </label>
            <input name="abffilter-action[sendmail]" type="submit" value="Submit" />
        </form>
        ';
        return $dsc . '<pre class="XML"><code>' . htmlspecialchars($form) . '</code></pre>'; //preg_replace(array('/\</', '/\>/'), array('&lt;', '&gt;'), $form);
    }


    public static function about()
    {
        return array(
            'name' => 'ABFFilter: Send unbann email',
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
        if (isset($_POST['abffilter-action']['sendmail'])) {
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
        $url = sprintf('%s/%s', URL , Symphony::Configuration()->get('unban-url', 'abffilter'));

        if (!$filter->sendMail($url)) {
            $result->setAttribute('result', 'error');
        } else {
            $result->setAttribute('result', 'success');
        }
    }
}

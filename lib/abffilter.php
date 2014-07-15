<?php

/**
 * This File is part of package name
 *
 * (c) author <email>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

/**
 * Class Abffilter
 * @author
 */
class AbfFilter
{
    /**
     * status
     *
     * @var string
     */
    const STAT_OK = 'passed';

    /**
     * status
     *
     * @var string
     */
    const STAT_ERROR = 'failed';

    /**
     * status
     *
     * @var mixed
     */
    protected $status;
    /**
     * message
     *
     * @var mixed
     */
    protected $message;
    /**
     * abf
     *
     * @var mixed
     */
    protected $abf;
    /**
     * _email_sent
     *
     * @var mixed
     */
    private $_email_sent = false;

    /**
     * __construct
     *
     * @param ABF $abf
     * @param MySQL $db
     */
    public function __construct(ABF $abf, MySQL $db)
    {
        $this->abf = $abf;
        $this->db = $db;
    }

    /**
     * sendMail
     *
     * @param string $url
     */
    public function sendMail($url)
    {
        $url = rtrim($url, '/') . '/';
        $ids = $this->db->fetchRow(0, "SELECT `me`.field_id, `mu`.field_id as unid FROM `tbl_fields_memberemail` as me JOIN `tbl_fields_memberusername` as mu LIMIT 1");
        $mailaddress = MySQL::cleanValue($_POST['email']);

        $tbl_entry_email    =  "tbl_entries_data_" . $ids['field_id'];
        $tbl_entry_username =  "tbl_entries_data_" . $ids['unid'];

        $author = $this->db->fetchRow(0, "
            SELECT `de`.value AS email, `du`.value AS username FROM `$tbl_entry_email`
            AS de LEFT JOIN `$tbl_entry_username` AS du
            ON (`de`.entry_id = `du`.entry_id)
            WHERE de.value = '$mailaddress'
        ");

        $failure = $this->abf->getFailureByIp();

        if (is_array($author) && isset($author['email']) &&
            is_array($failure) && isset($failure[0]) && isset($failure[0]->Hash)) {
            // safe run
            try {
                // use default values
                $email = Email::create();

                $email->recipients = $author['email'];
                $email->subject = __('Unban IP link');
                $email->text_plain =
                        __('Please follow this link to unban your IP: ') .
                        $url  . $failure[0]->Hash . '/' . PHP_EOL .
                        __('If you do not remember your password, follow the "forgot password" link on the login page.') . PHP_EOL .
                        __('The Symphony Team');

                // set error flag
                $this->_email_sent = $email->validate() && $email->send();

                $this->status  = self::STAT_OK;
                $this->message = __('Email successfully sent to %s', array($mailaddress));

            } catch (Exception $e) {
                // do nothing
                $this->_email_sent = false;
                $this->status  = self::STAT_ERROR;
                $this->message = __('Email could not be sent');
                return false;
            }
            // all ok, email sent.
            return true;
        } else {

            $this->status  = self::STAT_ERROR;
            $this->message = __('The email you provided could not be found');
            return false;
        }
    }

    /**
     * unbanWithHash
     *
     * @param string $hash
     */
    public function unbanWithHash($hash)
    {
        $hash = MySQL::cleanValue($hash);
        $this->status = self::STAT_ERROR;
        $this->message = __('IP cannot be unbanned');

        if (strlen($hash) !== 36) {
            return false;
        }

        $hashexists = $this->db->fetchRow(0, "SELECT hash FROM `tbl_anti_brute_force` WHERE hash = '$hash'");
        if (empty($hashexists) || !isset($hashexists['hash'])) {
            return false;
        }

		if ($result = $this->abf->unregisterFailure($hash)) {
            $this->status = self::STAT_OK;
            $this->message = __('IP successfully unbanned');
            return $result;
        }

        return false;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getMessage()
    {
        return $this->message;
    }
}

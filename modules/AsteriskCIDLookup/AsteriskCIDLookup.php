<?php

chdir(dirname(__FILE__) . '/../../');

include_once('vtlib/Vtiger/Module.php');
include_once('includes/http/Request.php');
include_once('include/database/PearDatabase.php');
include_once('config.inc.php');

# ini_set('display_errors', 1);
# ini_set('display_startup_errors', 1);
# error_reporting(-1);

class AsteriskCIDLookup
{

    function purge_number($number)
    {
        return preg_replace("/\D/", '', $number);
    }

    function show_result($adb, $query)
    {
        if ($adb->num_rows($query) > 0) {
            $cid = $adb->query_result($query, 0, 'cid');
            # < > chars are required because Asterisk's curl function will take unicode
            # chars only between ascii chars for some reason
            # you can use any other ascii chars from the start and at the end of the string
            printf("<%s>\r\n", $cid);
            exit(0);
        }
    }

    function send_no_auth()
    {
        header('WWW-Authenticate: Basic realm="CID Lookup"');
        header('HTTP/1.0 401 Unauthorized');
        exit(1);
    }

    function auth_enabled()
    {
        global $asterisk_cid_lookup;

        if (!is_array($asterisk_cid_lookup)) {
          return false;
        }
        if (!array_key_exists('user', $asterisk_cid_lookup)) {
          return false;
        }
        if (!array_key_exists('password', $asterisk_cid_lookup)) {
          return false;
        }
        return true;
    }

    function auth_was_sent()
    {
        if (!array_key_exists('PHP_AUTH_USER', $_SERVER)) {
          return false;
        }
        if (!array_key_exists('PHP_AUTH_PW', $_SERVER)) {
          return false;
        }
        return true;
    }

    function check_access()
    {
        global $asterisk_cid_lookup;

        if (!$this->auth_enabled()) {
            return true;
        }

        if (!$this->auth_was_sent()) {
            $this->send_no_auth();
        }

        if ($_SERVER['PHP_AUTH_USER'] != $asterisk_cid_lookup['user']) {
          $this->send_no_auth();
        }

        if ($_SERVER['PHP_AUTH_PW'] != $asterisk_cid_lookup['password']) {
          $this->send_no_auth();
        }
    }

    function lookup_users($adb, $number)
    {
        $sql = 'select concat( ifnull(concat(first_name, " "), ""), ifnull(last_name, "") ) as cid from vtiger_users where phone_home = ? or phone_mobile = ? or phone_work = ? or phone_other = ? or phone_fax = ? or phone_crm_extension = ? limit 1';
        $query = $adb->pquery($sql, array($number, $number, $number, $number, $number, $number));
        $this->show_result($adb, $query);
    }

    function lookup_accounts($adb, $number)
    {
        $sql = 'select accountname as cid from vtiger_account where phone = ? or otherphone = ? or fax = ? limit 1';
        $query = $adb->pquery($sql, array($number, $number, $number));
        $this->show_result($adb, $query);
    }

    function lookup_contacts($adb, $number)
    {
        $sql = 'select concat( ifnull(concat(firstname, " "), ""), ifnull(lastname, "") ) as cid from vtiger_contactdetails where phone = ? or mobile = ? or fax = ? limit 1';
        $query = $adb->pquery($sql, array($number, $number, $number));
        $this->show_result($adb, $query);
    }

    function process()
    {
        $request = new Vtiger_Request($_REQUEST);
        $this->check_access();
        $number = $request->get('number');
        $number = $this->purge_number($number);
        $adb = PearDatabase::getInstance();
        $this->lookup_users($adb, $number);
        $this->lookup_contacts($adb, $number);
        $this->lookup_accounts($adb, $number);
    }

    function check_if_enabled()
    {
        if (!vtlib_isModuleActive('AsteriskCIDLookup')) {
            exit(0);
        }
    }
}

$lookup = new AsteriskCIDLookup();
$lookup->check_if_enabled();
$lookup->process();
?>

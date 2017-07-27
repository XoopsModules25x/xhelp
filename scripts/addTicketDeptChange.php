<?php
require_once __DIR__ . '/../../../mainfile.php';

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    require_once XOOPS_ROOT_PATH . '/modules/xhelp/include/constants.php';
}
require_once XHELP_JPSPAN_PATH . '/JPSpan.php';       // Including this sets up the JPSPAN constants
require_once JPSPAN . 'Server/PostOffice.php';      // Load the PostOffice server
require_once XHELP_BASE_PATH . '/functions.php';

// Create the PostOffice server
$server = new JPSpan_Server_PostOffice();
$server->addHandler(new XHelpWebLib());

if (isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'], 'client') == 0) {

    // Compress the output Javascript (e.g. strip whitespace)
    define('JPSPAN_INCLUDE_COMPRESS', true);

    // Display the Javascript client
    $server->displayClient();
} else {
    // This is where the real serving happens...
    // Include error handler
    // PHP errors, warnings and notices serialized to JS
    require_once JPSPAN . 'ErrorHandler.php';

    // Start serving requests...
    $server->serve();
}

/**
 * Class XHelpWebLib
 */
class XHelpWebLib
{
    /**
     * @param $deptid
     * @return array
     */
    public function customFieldsByDept($deptid)
    {
        $deptid     = (int)$deptid;
        $hFieldDept = xhelpGetHandler('ticketFieldDepartment');
        $fields     =& $hFieldDept->fieldsByDepartment($deptid);

        $aFields = [];
        foreach ($fields as $field) {
            $aFields[] = $field->toArray();
        }

        return $aFields;
    }

    /**
     * @param $deptid
     * @param $ticketid
     * @return array
     */
    public function editTicketCustFields($deptid, $ticketid)
    {
        $deptid     = (int)$deptid;
        $hFieldDept = xhelpGetHandler('ticketFieldDepartment');
        $hTicket    = xhelpGetHandler('ticket');
        $ticket     =& $hTicket->get($ticketid);
        $custValues =& $ticket->getCustFieldValues();
        $fields     =& $hFieldDept->fieldsByDepartment($deptid);

        $aFields = [];
        foreach ($fields as $field) {
            $_arr                 =& $field->toArray();
            $_fieldname           = $_arr['fieldname'];
            $_arr['currentvalue'] = isset($custValues[$_fieldname]) ? $custValues[$_fieldname]['key'] : '';
            $aFields[]            = $_arr;
        }

        return $aFields;
    }

    /**
     * @param $deptid
     * @return array
     */
    public function staffByDept($deptid)
    {
        $mc    =& xhelpGetModuleConfig();
        $field = $mc['xhelp_displayName'] == 1 ? 'uname' : 'name';

        $deptid      = (int)$deptid;
        $hMembership = xhelpGetHandler('membership');
        $staff       =& $hMembership->xoopsUsersByDept($deptid);

        $aStaff   = [];
        $aStaff[] = [
            'uid'  => 0,
            'name' => _XHELP_MESSAGE_NOOWNER
        ];
        foreach ($staff as $s) {
            $aStaff[] = [
                'uid'  => $s->getVar('uid'),
                'name' => $s->getVar($field)
            ];
        }

        return $aStaff;
    }
}

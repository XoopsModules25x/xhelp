<!DOCTYPE html>
<html xml:lang="<{$xoops_langcode}>" lang="<{$xoops_langcode}>">
<head>
    <meta http-equiv="content-type" content="text/html; charset=<{$xoops_charset}>">
    <meta http-equiv="content-language" content="<{$xoops_langcode}>">
    <title><{$sitename}> <{$smarty.const._XHELP_TEXT_USER_LOOKUP}></title>
    <script type="text/javascript">
        <!--//
        function setUser(userInfo) {
            var uidDom = window.opener.xoopsGetElementById('user_id');
            var nameDom = window.opener.xoopsGetElementById('fullname');

            var uinfo = userInfo.split("^");

            uidDom.value = uinfo[0];
            nameDom.value = uinfo[1];
            <{if $xhelp_inadmin eq true}>
            window.opener.location.replace('<{$xhelp_adminURL}>/staff.php?op=manageStaff&uid=' + uinfo[0]);
            <{/if}>
            window.close();
            return;
        }

        function getSelectedRadioValue(radio) {
            if (radio.length) {
                for (var i = 0; i < radio.length; i++) {
                    if (radio[i].checked) {
                        return radio[i].value;
                    }
                }
            } else {
                return radio.value;
            }
        }

        //-->
    </script>
    <link rel="stylesheet" type="text/css" media="screen" href="<{$xoops_url}>/xoops.css">
    <link rel="stylesheet" type="text/css" media="screen" href="<{$xoops_themecss}>">
    <style type="text/css" media="all">
        body {
            margin: 0;
        }

        img {
            border: 0;
        }

        table {
            width: 100%;
            margin: 0;
        }

        a:link {
            color: #3a76d6;
            font-weight: bold;
            background-color: transparent;
        }

        a:visited {
            color: #9eb2d6;
            font-weight: bold;
            background-color: transparent;
        }

        a:hover {
            color: #e18a00;
            background-color: transparent;
        }

        table td {
            background-color: #ffffff;
            font-size: 12px;
            padding: 0;
            border-width: 0;
            vertical-align: top;
            font-family: Verdana, Arial, Helvetica, sans-serif;
        }

        table#imagenav td {
            vertical-align: bottom;
            padding: 5px;
        }

        table#imagemain td {
            border-right: 1px solid #c0c0c0;
            border-bottom: 1px solid #c0c0c0;
            padding: 5px;
            vertical-align: middle;
        }

        table#imagemain th {
            border: 0;
            background-color: #2F5376;
            color: #ffffff;
            font-size: 12px;
            padding: 5px;
            vertical-align: top;
            text-align: center;
            font-family: Verdana, Arial, Helvetica, sans-serif;
        }

        table#header td {
            width: 100%;
            background-color: #2F5376;
            vertical-align: middle;
        }

        table#header td#headerbar {
            border-bottom: 1px solid #c0c0c0;
            background-color: #dddddd;
        }

        div#pagenav {
            text-align: center;
        }

        div#footer {
            text-align: right;
            padding: 5px;
        }
    </style>
</head>

<body>

<{if $xhelp_viewResults neq true}>
    <div id='lookup'>
        <form method='post' action='lookup.php?admin=<{$xhelp_inadmin}>'>
            <table width='100%' border='1' cellpadding='0' cellspacing='2' id='searchtable' class='formButton'>
                <tr>
                    <th colspan='2'>
                        <img src="<{$xhelp_imagePath}>lookup.png"
                             alt="<{$smarty.const._XHELP_TEXT_LOOKUP_USER}>"><{$smarty.const._XHELP_TEXT_LOOKUP_USER}>
                    </th>
                </tr>
                <tr>
                    <td class='head' align='right'><{$smarty.const._XHELP_TEXT_SEARCH}></td>
                    <td class='even'>
                        <label>
                            <input type='text' name='searchText'>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td class='head' align='right'><{$smarty.const._XHELP_TEXT_SEARCHBY}></td>
                    <td class='even'>
                        <label>
                            <select name='subject'>
                                <option value='email'><{$smarty.const._XHELP_SEARCH_EMAIL}></option>
                                <option value='name'><{$smarty.const._XHELP_TEXT_REALNAME}></option>
                                <option value='uname'><{$smarty.const._XHELP_SEARCH_USERNAME}></option>
                                <option value='uid'><{$smarty.const._XHELP_SEARCH_UID}></option>
                            </select>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td class='head'></td>
                    <td class='even'>
                        <input type='submit' name='search' value='<{$smarty.const._XHELP_BUTTON_FIND_USER}>'
                               class='formButton'>
                        <input type='reset' value='<{$smarty.const._XHELP_BUTTON_RESET}>' class='formButton'>
                    </td>
                </tr>
            </table>
        </form>
    </div>
<{else}>
    <div id="viewResults">
        <form method="post" name="frmResults" id="frmResults" action=""
              onsubmit="setUser(getSelectedRadioValue(frmResults.logFor));">


            <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
                <tr>
                    <th colspan="4">
                        <img src="<{$xhelp_imagePath}>lookup.png"><{$smarty.const._XHELP_TEXT_LOOKUP_USER}>
                    </th>
                </tr>
                <tr class="head">
                    <td>
                        <{$smarty.const._XHELP_TEXT_USERID}>
                    </td>
                    <td>
                        <{$smarty.const._XHELP_TEXT_NAME}>
                    </td>
                    <td>
                        <{$smarty.const._XHELP_TEXT_REALNAME}>
                    </td>
                    <td>
                        <{$smarty.const._XHELP_TEXT_EMAIL}>
                    </td>
                </tr>
                <{if $xhelp_matchCount neq 0}>
                    <{foreach from=$xhelp_matches item=user}>
                        <tr class="<{cycle values="odd,even"}>">
                            <td>
                                <label>
                                    <input name="logFor" value="<{$user.uid}>^<{$user.uname}>" type="radio"
                                           class="formButton">
                                </label>
                                <{$user.uid}>
                            </td>
                            <td>
                                <{$user.uname}>
                            </td>
                            <td>
                                <{$user.name}>
                            </td>
                            <td>
                                <{$user.email}>
                            </td>
                        </tr>
                    <{/foreach}>
                    <tr>
                        <td colspan="4" class="foot">
                            <input type="submit" name="gotUser"
                                   value="<{if $xhelp_inadmin eq true}><{$smarty.const._XHELP_TEXT_ADD_STAFF}><{else}><{$smarty.const._XHELP_BUTTON_LOG_USER}><{/if}>"
                                   class="formButton">
                        </td>
                    </tr>
                <{else}>
                    <tr>
                        <td>
                            <{$smarty.const._XHELP_TEXT_NO_USERS}>
                        </td>
                    </tr>
                <{/if}>
            </table>
        </form>
        <a href="lookup.php"><{$smarty.const._XHELP_TEXT_SEARCH_AGAIN}></a>
    </div>
<{/if}>
</body>
</html>

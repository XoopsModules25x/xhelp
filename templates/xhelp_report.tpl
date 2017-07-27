<{include file='db:xhelp_staff_header.tpl'}>

<div id="reportsList">
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
        <tr>
            <th colspan="2">
                <img src="<{$xhelp_imagePath}>report.png" title="<{$smarty.const._XHELP_TEXT_REPORTS}>">
                <{$smarty.const._XHELP_TEXT_REPORTS}>
            </th>
        </tr>
        <tr class="head">
            <td><{$smarty.const._XHELP_TEXT_REPORT_NAME}></td>
            <td><{$smarty.const._XHELP_TEXT_DESCRIPTION}></td>
        </tr>
        <{foreach from=$xhelp_reports item=report key=file}>
            <tr class="even">
                <td><a href="<{$xhelp_baseURL}>/report.php?op=run&amp;name=<{$file}>"><{$report.name}></a></td>
                <td><{$report.description}></td>
            </tr>
        <{/foreach}>
    </table>
</div>

<table id="xhelp_dept_perf">
    <{foreach from=$block.departments item=dept}>
        <tr>
            <td><a href="<{$dept.url}>"><{$dept.name}></a> (<{$dept.tickets}>)</td>
            <td><{if $block.use_img}><img src="<{$dept.img}>" alt="<{$dept.name}>"><{else}><{$dept.tickets}><{/if}>
            </td>
        </tr>
    <{/foreach}>
</table>

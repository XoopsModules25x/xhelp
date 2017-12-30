var XHELP_CONTROL_TXTBOX = 0;
var XHELP_CONTROL_TXTAREA = 1;
var XHELP_CONTROL_SELECT = 2;
var XHELP_CONTROL_MULTISELECT = 3;
var XHELP_CONTROL_YESNO = 4;
var XHELP_CONTROL_CHECKBOX = 5;
var XHELP_CONTROL_RADIOBOX = 6;
var XHELP_CONTROL_DATETIME = 7;
var XHELP_CONTROL_FILE = 8;

var d = document;

function cE(t) {
    var l = d.createElement(t);
    return l;
}
function gE(n) {
    var l = xoopsGetElementById(n);
    return l;
}

function selectAll(formObj, fieldname, isInverse) {
    if (fieldname.length == 0) {
        for (var i = 0; i < formObj.length; i++) {
            fldObj = formObj.elements[i];
            if (fldObj.type == 'checkbox') {
                fldObj.checked = isInverse;
            }
        }
    } else {
        for (var i = 0; i < formObj.length; i++) {
            fldObj = formObj.elements[i];
            if (fldObj.type == 'checkbox') {
                if (fldObj.name.indexOf(fieldname) > -1) {
                    fldObj.checked = isInverse;
                }
            }
        }
    }
}

function xhelp\RoleCustOnClick(frmName, roleName, roleParam, joinChr, className) {
    if (joinChr.length == 0) {
        joinChr = '&amp;';
    }
    var aRoles = Array();
    var ele = document.forms[frmName].elements;
    var re = new RegExp(roleParam + '=([0-9,])*', 'gi');
    var newUrl = '';
    for (var i = 0; i < ele.length; i++) {
        var chk = ele[i];
        if ((chk.type == 'checkbox') && (chk.name == roleName)) {
            if (chk.checked == true) {
                aRoles[aRoles.length] = chk.value;
            }
        }
    }
    if (aRoles.length) {
        newUrl = roleParam + '=' + aRoles.join(',');
    }
    /* Loop through <a> links with class of className */
    ele = document.getElementsByTagName('a');
    for (i = 0; i < ele.length; i++) {
        var anc = ele[i];
        if (anc.className == className) {
            if (anc.href.indexOf(roleParam) > -1) {
                anc.href = anc.href.replace(re, newUrl);
            } else {
                anc.href += joinChr + newUrl;
            }
        }
    }

}

function xhelpPortOnChange(srvtype, portfld) {
    fld = gE(portfld);
    switch (srvtype) {
        case 'POP3':
            fld.value = '110';
            break;

        case 'IMAP':
            fld.value = '143';
            break;
    }
}

function xhelpDOMAddEvent(obj, evType, fn, useCapture) {
    if (window.opera && obj.addEventListener) {
        obj.addEventListener(evType, fn, false);
        return true;
    } else if (obj.addEventListener) {
        obj.addEventListener(evType, fn, useCapture);
        return true;
    } else if (obj.attachEvent) {
        var r = obj.attachEvent("on" + evType, fn);
        return r;
    } else {
        alert("Handler could not be attached");
    }
}

function xhelpDOMRemoveEvent(obj, evType, fn, useCapture) {
    if (obj.removeEventListener) {
        obj.removeEventListener(evType, fn, useCapture);
        return true;
    } else if (obj.detachEvent) {
        var r = obj.detachEvent("on" + evType, fn);
        return r;
    } else {
        alert("Handler could not be removed");
    }
}

function xhelpGetHTTPObject() {
    var xmlhttp;
    /*@cc_on
     @if (@_jscript_version >= 5)
     try {
     xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
     } catch (e) {
     try {
     xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
     } catch (E) {
     xmlhttp = false;
     }
     }
     @else
     xmlhttp = false;
     @end @*/
    if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
        try {
            xmlhttp = new XMLHttpRequest();
        } catch (e) {
            xmlhttp = false;
        }
    }
    return xmlhttp;
}

function xhelpFillStaffSelect(sel, values) {
    if (values.length > 0) {
        var val = sel.value;
        var uid = '';
        var uname = '';
        var idx = -1;
        sel.options.length = 0;

        for (var n = 0; n < values.length; n++) {
            uid = values[n]['uid'];
            uname = values[n]['name'];
            if (uid == val) {
                idx = n;
            }
            /* Check if uid is the selected one */
            sel[n] = new Option(uname, uid);
        }
        if (idx > -1) {
            sel.value = val;
            sel.selectedIndex = idx;
        }
    }
}

function xhelpFillSelect(sel, values) {
    if (values.length > 0) {
        var val = sel.value;
        var myKey = '';
        var myValue = '';
        var idx = -1;
        sel.options.length = 0;

        for (var n = 0; n < values.length; n++) {
            myKey = values[n]['key'];
            myValue = values[n]['value'];
            if (myKey == val) {
                idx = n;
            }
            sel[n] = new Option(myValue, myKey);
        }
        if (idx > -1) {
            sel.value = val;
            sel.selectedIndex = idx;
        }
    }
}

function xhelpFillCustomFlds(tbl, result, before) {
    var flds = xhelpGetElementsByClassName(tbl, 'custfld_value');
    var values = Array();

    for (var n = 0; n < flds.length; n++) {
        values[n] = Array(flds[n].id, flds[n].value);
    }
    var flds = tbl.getElementsByTagName('tr');
    var fld_classname = '';
    for (n = flds.length - 1; n > -1; n--) {
        if (flds[n].attributes) {
            fld_classname = flds[n].className;

            if (fld_classname && fld_classname.indexOf('custfld') > -1) {
                tbl.removeChild(flds[n]);
            }
        }
    }

    if (result.length > 0) {
        for (n = 0; n < result.length; n++) {
            data = result[n];

            if (data != null) {
                name = '';
                desc = '';
                fieldName = '';
                defaultValue = '';
                controlType = 0;
                required = 0;
                fieldLength = 0;
                currentValue = '';
                values = Array();
                weight = 0;

                name = data['name'];
                desc = data['desc'];
                fieldName = data['fieldname'];
                defaultValue = data['defaultvalue'];
                currentValue = data['currentvalue'];
                controlType = data['controltype'];
                required = data['required'];
                weight = data['weight'];
                fieldLength = data['fieldlength'];
                if (data['fieldvalues'] != null) {
                    fieldValues = data['fieldvalues'];

                    for (o = 0; o < fieldValues.length; o++) {
                        values[o] = Array(fieldValues[o][0], fieldValues[o][1]);
                    }
                }
                if (currentValue != '') {
                    for (m = 0; m < values.length; m++) {
                        if (values[m][0] == fieldName) {
                            currentValue = values[m][1];
                            break;
                        }
                    }
                }
                inp = xhelpCreateInpField(controlType, fieldName, defaultValue, currentValue, fieldLength, values);
                tbl.insertBefore(xhelpCreateCustFldRow(name, desc, inp), before);
            }

        }
    }
}

function xhelpGetElementsByClassName(parentEle, className) {
    var ele = Array();
    if (parentEle.hasChildNodes()) {
        var chlds = parentEle.childNodes;
        var chld_class = '';
        for (var n = 0; n < chlds.length; n++) {
            if (chlds[n].attributes) {
                chld_class = chlds[n].getAttribute('class');

                if (chld_class && chld_class.indexOf(className) > -1) {
                    ele[ele.length] = chlds[n];
                }
            }
            if (chlds[n].hasChildNodes()) {
                ele.concat(xhelpGetElementsByClassName(chlds[n], className));
            }

        }
    }
    return ele;
}

function xhelpCreateInpField(controlType, fieldName, defaultValue, currentValue, fieldLength, values) {
    var value = (currentValue.length > 0 ? currentValue : defaultValue);
    var length = (fieldLength < 50 ? fieldLength : 50);
    var ele = null;
    switch (controlType) {
        case '0':
            ele = xhelpCreateTextBox(fieldName, value, length, fieldLength);
            break;
        case '8':
            ele = xhelpCreateFile(fieldName, length);
            break;
        case '1':
            ele = xhelpCreateTextArea(fieldName, value, length, 5);
            break;
        case '2':
        case '3':
            ele = xhelpCreateSelect(fieldName, values, value, 1, false);
            break;
        case '5':
            ele = xhelpCreateCheckbox(fieldName, values, value);
            break;
        case '6':
        case '4':
            ele = xhelpCreateRadiobox(fieldName, values, value);
            break;
        case '7':
            ele = xhelpCreateDateTime(fieldName, value);
            break;
    }
    return ele;
}

function xhelpCreateTextArea(fieldName, value, cols, rows) {
    var l = cE(document.all ? '<textarea name="' + fieldName + '">' : 'textarea');
    l.id = fieldName;
    l.name = fieldName;
    l.setAttribute('rows', rows);
    l.setAttribute('cols', cols);
    l.value = value;
    return l;
}

function xhelpCreateTextBox(fieldName, value, length, maxLength) {
    var l = cE(document.all ? '<input name="' + fieldName + '">' : 'input');
    l.setAttribute('type', 'text');
    l.id = fieldName;
    l.name = fieldName
    l.setAttribute('size', length);
    l.setAttribute('maxlength', maxLength);
    l.value = value;
    return l;
}

function xhelpCreateFile(fieldName, length) {
    var l = cE(document.all ? '<input name="' + fieldName + '">' : 'input');
    l.setAttribute('type', 'file');
    l.id = fieldName;
    l.name = fieldName;
    l.setAttribute('size', length);
    return l;
}

function xhelpCreateSelect(fieldName, values, value, size, multiple) {
    var l = cE(document.all ? '<select name="' + fieldName + '">' : 'select');
    var i = -1;
    l.id = fieldName;
    l.name = fieldName;
    l.setAttribute('size', size);
    if (multiple == true) {
        l.setAttribute('multiple', 'multiple')
    }
    for (var n = 0; n < values.length; n++) {
        if (value == values[n][0]) {
            i = n;
        }
        l[n] = new Option(values[n][1], values[n][0]);
    }
    if (i > -1) {
        l.value = value;
        l.selectedIndex = i;
    }
    return l;
}

function xhelpCreateCheckbox(fieldName, values, value) {
    var ele = Array();
    var l = null;
    for (var n = 0; n < values.length; n++) {
        l = cE(document.all ? '<input name="' + fieldName + '">' : 'input');
        l.id = fieldName + n;
        l.name = fieldName + '[]';
        l.value = values[n][0];
        if (isArray(value)) {
            l.checked = xhelpInArray(l.value, value);
        } else {
            if (value == values[n][0]) {
                l.checked = true
            }
        }
        ele[n] = Array(l, values[n][1]);
    }
    return ele;
}

function xhelpCreateRadiobox(fieldName, values, value) {
    var ele = Array();
    var l = null;
    for (var n = 0; n < values.length; n++) {
        l = cE(document.all ? '<input name="' + fieldName + '">' : 'input');
        l.setAttribute('type', 'radio');
        l.id = fieldName + n;
        l.name = fieldName;
        l.value = values[n][0];
        if (value == values[n][0]) {
            l.checked = true
        }
        ele[n] = Array(l, values[n][1]);
    }
    return ele;
}

function xhelpCreateDateTime(fieldName, value) {
    var l = xhelpCreateTextBox(fieldName, value, 50, 25);
    return l;
}

function xhelpCreateCustFldRow(name, description, ele) {
    var tr = cE('tr');
    var td1 = cE('td');
    var td2 = cE('td');

    tr.className = 'custfld';
    td1.className = 'head';
    td1.appendChild(d.createTextNode(name));
    if (description != "") {
        td1.appendChild(cE('br'));
        td1.appendChild(cE('br'));
        td1.appendChild(d.createTextNode(description));
    }

    td2.className = 'even';
    if (isArray(ele) == false) {
        td2.appendChild(ele);
    } else {
        for (var i = 0; i < ele.length; i++) {

            lbl = cE('label');
            lbl.setAttribute('for', ele[i][0].id);
            lbl.appendChild(ele[i][0]);
            lbl.appendChild(d.createTextNode(ele[i][1]));
            td2.appendChild(lbl);
            if (i < ele.length - 1) {
                td2.appendChild(cE('br'));
            }
        }

        /* tv = cE('table');
         tv.setAttribute('border', 0);
         tv.setAttribute('width', '100%');
         for (var i = 0; i < ele.length; i=i+3) {
         tr = cE('tr');
         var j = 0
         for (j = 0; j < 3; j++) {
         if (i + j >= ele.length) {
         continue;
         }
         tdv = cE('td');
         lbl = cE('label');
         lbl.setAttribute('for', ele[i+j][0].id);
         lbl.appendChild(d.createTextNode(ele[i+j][1]));

         tdv.appendChild(ele[i+j][0], lbl);
         tr.appendChild(tdv);
         }
         tv.appendChild(tr);
         }
         td2.appendChild(tv); */
    }

    tr.appendChild(td1);
    tr.appendChild(td2);
    return tr;
}

function xhelpInArray(needle, haystack) {
    for (var i = 0; i < haystack.length; i++) {
        if (needle == haystack[i]) {
            return true;
        }
    }
    return false;
}

function isArray(obj) {
    if (obj && obj.constructor) {
        if (obj.constructor.toString().indexOf("Array") == -1) {
            return false;
        } else {
            return true;
        }
    } else {
        return false;
    }
}

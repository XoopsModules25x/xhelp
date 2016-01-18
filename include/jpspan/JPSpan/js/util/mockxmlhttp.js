// $Id: mockxmlhttp.js,v 1.1 2005/06/21 15:31:21 eric_juden Exp $
// Used for unit testing - simulates XmlHttpRequest API
/**@
* include 'util/mock.js';
*/

function JPSPan_Util_MockXMLHttpRequest() {
    var oParent = new JPSpan_Util_MockObject();
    oParent.readyState = null;
    oParent.responseText = null;
    oParent.responseXml = null;
    oParent.status = null;
    oParent.statusText= null;
    oParent.addMethod('onreadystatechange');
    oParent.addMethod('abort');
    oParent.addMethod('addEventListener');
    oParent.addMethod('dispatchEvent');
    oParent.addMethod('getAllResponseHeaders');
    oParent.addMethod('getResponseHeader');
    oParent.addMethod('open');
    oParent.addMethod('overrideMimeType');
    oParent.addMethod('removeEventListener');
    oParent.addMethod('send');
    oParent.addMethod('setRequestHeader');
    return oParent;
};

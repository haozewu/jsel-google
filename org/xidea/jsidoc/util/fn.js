/*
 * Compressed by JSA(www.xidea.org)
 */
function xmlReplacer(_){switch(_){case"<":return"&lt;";case">":return"&gt;";case"&":return"&amp;";case"'":return"&#39;";case'"':return"&#34;";}}function loadText(A){var _=new XMLHttpRequest();_.open("GET",A,false);try{_.send(null);if(_.status>=200&&_.status<300||_.status==304||!_.status){return _.responseText;}else{}}catch(B){}finally{_.abort();}}
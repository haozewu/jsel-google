/*
 * Compressed by JSA(www.xidea.org)
 */
if(typeof window.confirm!="function"){function __$4(B,_,A){return $JSI.impl.log(B,_,A);}}else{__$4=function(C,A,B){var _=__$0[A];return confirm(_+":"+B+"\n\n继续弹出 ["+C+"]"+_+" 日志?\r\n");};}function __$5(_){return function(){if(_>this.level){var A=this.format.apply(this,arguments);if(_>this.userLevel){if(__$4(this.title,_,A)===false){this.userLevel=_;}}}return A;};}function __$3(A,_){this.title=A;this.level=_;}__$3.prototype={userLevel:1,filters:[],clone:function(A){var _=new __$3(A,this.level);return _;},addFilter:function(_){this.filters.push(_);},dir:function(A){var _=[];for(A in A){_.push(A);}this.info(_.join("\n"));},format:function(_){for(var A=[],B=0;B<arguments.length;B++){_=arguments[B];if(_ instanceof Array){A.push("[",_,"]\n");}else{if(_ instanceof Object){A.push(_,"{");for(var C in _){A.push(C,":",_[C],",");}A.push("}\n");}else{A.push(_,"\n");}}}A=A.join("");for(B=0;B<this.filters.length;B++){A=this.filters[B].call(this,A);}return A;}};var $log=new __$3("",1),__$0="trace,debug,info,warn,error,fatal".split(","),__$2=__$0.length;while(__$2--){var __$1=__$0[__$2];__$3.prototype[__$1]=__$5(__$2);}var console=$log;
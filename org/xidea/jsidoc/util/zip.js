/*
 * Compressed by JSA(www.xidea.org)
 */
function Zip(_){this.members=[];this.comment=_||"";}Zip.prototype={mimeType:"application/zip",addText:function(D,C){var B=__$R(C),A=this.compressImpl?this.compressImpl(B):B,_=this.compressMethod||0,E=new __$E(__$R(D),B);return __$O(this,E);},toByteArray:function(){var A=this.members,_=[],C=[];for(var B=0;B<A.length;B++){C.push(_.length);__$G(_,A[B]);__$H(_,A[B]);}var D=_.length;for(B=0;B<A.length;B++){__$K(_,A[B],C[B]);}var E=_.length;__$Q(_,101010256,4);__$Q(_,0,2);__$Q(_,0,2);__$Q(_,A.length,2);__$Q(_,A.length,2);__$Q(_,E-D,4);__$Q(_,D,4);var F=__$R(this.comment);__$Q(_,F.length,2);__$U.apply(_,F);return _;},toDataURL:function(){if(typeof btoa=="function"&&btoa(1)=="MQ=="){var _=btoa(String.fromCharCode.apply(0,this.toByteArray()));}else{_=__$V(this.toByteArray());}return["data:",this.mimeType,";base64,",_].join("");},constructor:Zip};var __$T="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=".split("");function __$V(B){var F=[],A=0;while(A<B.length){var C=B[A++],D=B[A++],_=B[A++],E=(C<<16)+(D<<8)+(_||0);F.push(__$T[(E>>18)&63],__$T[(E>>12)&63],__$T[isNaN(D)?64:(E>>6)&63],__$T[isNaN(_)?64:E&63]);}return F.join("");}function __$R(A){for(var D=[],_=0,C=A.length;_<C;_++){var B=A.charCodeAt(_);if(B<128){D.push(B);}else{if(B<2048){D.push(192|(B>>>6),128|(B&63));}else{D.push(224|((B>>>12)&15),128|((B>>>6)&63),128|(B&63));}}}return D;}function __$O(_,A){_.members.push(A);return A;}var __$U=Array.prototype.push,__$I=[],__$F=3988292384;for(var __$L=0,__$N;__$L<256;__$L++){__$N=__$L;for(var __$J=0;__$J<8;__$J++){if(__$N&1){__$N=(__$N>>>1)^__$F;}else{__$N=__$N>>>1;}}__$I[__$L]=__$N;}function __$Q(A,_,C){for(var B=0;B<C;B++){A.push(_>>(B*8)&255);}}function __$M(_,A){_.date=((A.getFullYear()-1980)<<9)|((A.getMonth()+1)<<5)|(A.getDate());_.time=(A.getHours()<<5)|(A.getMinutes()<<5)|(A.getSeconds()>>1);}function __$S(_){var B=4294967295;for(var A=0;A<_.length;A++){B=(B>>>8)^__$I[_[A]^(B&255)];}return~B;}function __$H(_,A){__$U.apply(_,A.data[1]);}function __$G(_,A){__$Q(_,67324752,4);__$Q(_,10,2);__$Q(_,0,2);__$Q(_,A.method,2);__$Q(_,A.time,2);__$Q(_,A.date,2);__$Q(_,A.crc32,4);__$Q(_,A.data[1].length,4);__$Q(_,A.data[0].length,4);__$Q(_,A.path.length,2);__$Q(_,A.extra.localFile.length,2);__$U.apply(_,A.path);__$U.apply(_,A.extra.localFile);return _;}function __$K(_,B,A){__$Q(_,33639248,4);__$Q(_,791,2);__$Q(_,10,2);__$Q(_,0,2);__$Q(_,B.method,2);__$Q(_,B.time,2);__$Q(_,B.date,2);__$Q(_,B.crc32,4);__$Q(_,B.data[1].length,4);__$Q(_,B.data[0].length,4);__$Q(_,B.path.length,2);__$Q(_,B.extra.centralDirectory.length,2);__$Q(_,0,2);__$Q(_,0,2);__$Q(_,0,2);__$Q(_,B.externalFileAttributes,4);__$Q(_,A,4);__$U.apply(_,B.path);__$U.apply(_,B.extra.centralDirectory);}function __$E(A,_,C,B){this.path=A;C=C||_;this.crc32=__$S(_);this.data=[_,C];this.method=B||0;this.externalFileAttributes=33188<<16;this.extra=new __$P;__$M(this,new Date);}function __$P(){this.localFile=[];this.centralDirectory=[];}__$P.prototype={append:function(_){__$U.apply(this.localFile,_.localFile);__$U.apply(this.centralDirectory,_.centralDirectory);},constructor:__$P};
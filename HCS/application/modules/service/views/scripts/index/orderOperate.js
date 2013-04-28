function test(){alert("test");}
function del($id){
    $url='/service/index/order/opt/del/id/'+$id;
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {            
            var obj = JSON.parse(xmlhttp.responseText);
            if(obj.gw.ok=="1"){
               
                swichLight(obj.target[0].status,"#s1","#light1");
                swichLight(obj.target[1].status,"#s2","#light2");
                swichLight(obj.target[2].status,"#s3","#light3");
                swichLight(obj.target[3].status,"#s4","#light4");
            }
        }
    }
    xmlhttp.open("GET",$url,true);
    xmlhttp.send();
}
function loaddata(){
    $url='/service/index/order/opt/list/json/1';
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            alert(xmlhttp.responseText);
            var obj = JSON.parse(xmlhttp.responseText);
            if(obj.gw.ok=="1"){
               
            }
        }
    }
    xmlhttp.open("GET",$url,true);
    xmlhttp.send();
}
function confirmOrder(){
    $url='/service/index/order/opt/confirm/';
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            alert(xmlhttp.responseText);
            var obj = JSON.parse(xmlhttp.responseText);
            if(obj.gw.ok=="1"){
               
            }
        }
    }
    xmlhttp.open("GET",$url,true);
    xmlhttp.send();
}
function add($id){	
     $url='/service/index/order/opt/add/id/'+$id;
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {            
            var obj = JSON.parse(xmlhttp.responseText);
            if(obj.gw.ok=="1"){
               
                swichLight(obj.target[0].status,"#s1","#light1");
                swichLight(obj.target[1].status,"#s2","#light2");
                swichLight(obj.target[2].status,"#s3","#light3");
                swichLight(obj.target[3].status,"#s4","#light4");
            }
        }
    }
    xmlhttp.open("GET",$url,true);
    xmlhttp.send();
}
function delOne($id){	
     $url='/service/index/order/opt/add/num/-1/id/'+$id;
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {            
            var obj = JSON.parse(xmlhttp.responseText);
            if(obj.gw.ok=="1"){               
                swichLight(obj.target[0].status,"#s1","#light1");
                swichLight(obj.target[1].status,"#s2","#light2");
                swichLight(obj.target[2].status,"#s3","#light3");
                swichLight(obj.target[3].status,"#s4","#light4");
            }
        }
    }
    xmlhttp.open("GET",$url,true);
    xmlhttp.send();
}

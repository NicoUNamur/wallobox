<?php
    include('./class/auth.inc.php');
    $do_gzip_compress=Header_Compress();
    Header("Content-type: text/css"); ?>

body,h1,h2,h3,ul,li,div,form,a,p{
    margin:0px 0px 0px 0px;
    padding:0px 0px 0px 0px;
}

body{
	font-family:"Calibri",Georgia,Serif;
	font-size:8pt;
	color:black;
}

table, tr, td, th{
	border:solid 1px black; border-collapse:collapse;
}
 
th { background-color: darkblue; color: white; }
th>a{ color: white; }

table{
border:solid 1px black; border-collapse:collapse;}

a{
	color:black /*#692609*/;
	font-weight:bold;
}
a:hover{
	color:#A96649;
}

img{border:none;}

.hidden , h1, nav , .boutton , .actionColumn , hr , .action {
	display:none;
}

label , .label , .value{
	display:inline;
	float:left;
}
.label>span{
	display:block;
	font-weight: bold ;
	width:250px;
}

.value>span{
	display:block;
	left:250px;
	height:100%;
}
input{
	color:black /*#692609*/;
	width:150px;
	margin:0px 0px 0px 3px;
}

.erreur{
	display:block;
	background:pink;
	border: red solid 1px;
	text-color:red;
	text-weight:bold;
	padding:5px;
	margin:10px;
}

.close{
    z-index:20;
    background:url("./images/close.gif") no-repeat;
    height:15px;
    width:15px;
}


div{
    z-index:1;
}

#content>fieldset{
	display:block;
	height:95%;
	overflow:auto;
}

div.autocomplete{
    position:absolute;
    width:250px;
    background:white;
    border:1px solid #888;
    margin:0px;
    padding:0px;
}

div.autocomplete ul{
    list-style-type:none;
    margin:0px;
    padding:0px;
}

div.autocomplete>ul>li{
    list-style-type:none;
    width:95%;
    display:block;
    margin:0;
    cursor:default;
    padding:0.1em 0.5ex;
    font-family:sans-serif;
    font-size:80%;
    color:#444;
    height:1.5em;
    line-height:1.5em;
}

div.autocomplete ul li.selected{
    background-color:blue; /*#ffb;*/
    color:white;
}

.vignette{
	width:10%;	
}

.deuxcol{
position:relative;
display:inline;
float:left;
width:49%;
}
.troiscol{
position:relative;
display:inline;
float:left;
width:33%;
}

.number_to_sum{
	text-align:right;
	/*number-format:#.00;*/
}

.clearboth{
    clear:both;
}

<?php    Footer_Compress($do_gzip_compress); ?>

<?php
    include('./class/auth.inc.php');
    $do_gzip_compress=Header_Compress();
    Header("Content-type: text/css"); ?>

body,h1,h2,h3,ul,li,div,form,a,p{
    margin:0px 0px 0px 0px;
    padding:0px 0px 0px 0px;
}

h1{
	background:url("cr-logo.png") no-repeat;
	height:45px;
}

h1 > span {
	display:none;
}

body{
	font-family:"Times New Roman",Georgia,Serif;
	color:black /*#692609*/;
}

#body{
	position:absolute;
	display:block;
	width:100%;
	min-width:640px;
	height:100%;
}

hr{
	height:6px;
	border:0px;
}

td{
	border-bottom:1px solid black /*#692609*/;;
}

a{
	color:black /*#692609*/;
	font-weight:bold;
}
a:hover{
	color:#A96649;
}

img{border:none;}

.hidden{
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
	width:250px;
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

.hidden{
    display:none;
}

div{
    z-index:1;
}

nav{
	position:absolute;
	left:10px;
	top:60px;
	bottom:10px;
	display:block;
	width:250px;
}

nav>fieldset>ul>li{
    list-style-type:none;
	padding:2px;
}

.selnav{
	background-color:#FF4444;
	color:#FFFFFF;
}
.selnav>a{
	color:#FFFFFF;
}
.selnav>a:hover{
	color:#A96649;
}

#content{
	display:block;
	position:absolute;
	left:270px;
	top:60px;
	bottom:10px;
	right:10px;
	display:block;
}
#content>fieldset{
	display:block;
	height:95%;
	overflow:auto;
}

.boutton{
	position:relative;
	top:10px;
	background:lightgrey;
	border:1px solid black;
	padding:2px;
	margin:10px;
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
    /*height:1.5em;*/
    line-height:1.5em;
}

div.autocomplete ul li.selected{
    background-color:blue; /*#ffb;*/
    color:white;
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


.clearboth{
    clear:both;
}

.vignette{
	height:100px;
}
.grandeImage{
	height:500px;
}

/* Autocomplete
----------------------------------*/
.ui-autocomplete { position: absolute; cursor: default; }
.ui-autocomplete-loading { background: white url('/img/ui-anim_basic_16x16.gif') right center no-repeat; }

/* workarounds */
* html .ui-autocomplete { width:1px; } /* without this, the menu expands to 100% in IE6 */

/* Menu
----------------------------------*/
.ui-menu {
        list-style:none;
        padding: 10px;
        margin: 0;
        display:block;
        //width:227px;
}
.ui-menu .ui-menu {
        margin-top: -3px;
}
.ui-menu .ui-menu-item {
        margin:0;
        padding: 0;
        //width: 200px;
		border:1px solid black;
}
.ui-menu .ui-menu-item a {
        text-decoration:none;
		//display:block;
        //padding:.2em .4em;
        //line-height:1.5;
        zoom:1;
}
.ui-menu .ui-menu-item a.ui-state-hover,
.ui-menu .ui-menu-item a.ui-state-active {
        margin: -1px;
}
.ui-menu img{
		height:80px;
}
#login>form>p>label{
	width:150px;
}
<?php    Footer_Compress($do_gzip_compress); ?>
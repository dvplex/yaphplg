<?
//Your local autonomous system
$local_as='AS31287';

//HTML Title
$html_title=$local_as.' Looking glass';

//MYSQL configuration
$mysql_host='localhost';
$mysql_user='root';
$mysql_password='';
$mysql_db='lg';

//Nodes ([sequence],name,ip)
$nodes=array();
$nodes[]=array('name'=>'router1','ip'=>'10.10.10.1','pass'=>'123456');
$nodes[]=array('name'=>'router2','ip'=>'10.10.10.2','pass'=>'123456');

//Maximum prefixes to show when clicking to show neighbor routes
$prefix_count='15000';

//Rsclient configuration
$rsclient='yes';

//Company logo
$company_logo_type='text'; //types are text or img
$company_logo_src='<a href="http://www.ipacct.com/" target=_blank><span style="background-color: #fff"><font face="ipacct" size="200"><font color="#cc0101">IP</font><font color="#002e5d">ACCT</font></font></span></a>';




?>

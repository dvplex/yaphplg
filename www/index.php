<?
//{{{ Inital setup and includes
header("Cache-Control: no-cache, must-revalidate");
include('../lib/functions.smarty.php');
include('../inc/config.php');
include("../lib/lg.php");
$cmd='';
$command='';
if (isset($_GET['cmd']))
	$cmd=$_GET['cmd'];
if (isset($_GET['cmd']))
	$cmd=$_GET['cmd'];
//}}}

//{{{ Show ip bgp summary 
switch ($cmd) {
	case 'summary':
		$pass=getNodePwd($_GET['router'],$nodes);
		$bgp=lg_bgp($_GET['router'],'2605',$pass,'show ip bgp summary');	
		$command='show ip bgp summary';
		assign('command',$command);
		assign('router',$_GET['router']);
		assign('ref','summary');
		assign('bgp',$bgp);
	break;
//}}}

//{{{ Show ip bgp neighbors x.x.x.x/xx advertised-routes
	case 'sibnar':
		$neighbor=$_GET['addr'];
		$host=$_GET['router'];
		$pass=getNodePwd($host,$nodes);
		$sibnr=lg_bgp($host,'2605',$pass,'show ip bgp neighbors '.$neighbor.' advertised-routes');	
		assign('router',$_GET['router']);
		if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/',$_GET['addr']))
			$command='show ip bgp neighbors '.$_GET['addr'].' advertised-routes';
		else
			$command='<font color="red">Please specify IP address</font>';
	
		assign('command',$command);
		assign('ref','sibnr');
		assign('sibnr',$sibnr);
	break;
//}}}

//{{{ Show ip bgp neighbors x.x.x.x/xx routes
	case 'sibnrr':
	case 'sibnr':
		if (isset($_GET['n'])) {
			$neighbor=$_GET['n'];
			$host=$_GET['r'];
			$pass=getNodePwd($host,$nodes);
			$sibnr=lg_bgp($host,'2605',$pass,'show ip bgp neighbors '.$neighbor.' routes');	
			assign('command',$command);
			assign('router',$_GET['r']);
			$command='show ip bgp neighbors '.$_GET['n'].' routes';
		}
		if (isset($_GET['addr'])) {
			$neighbor=$_GET['addr'];
			$host=$_GET['router'];
			$pass=getNodePwd($host,$nodes);
			$sibnr=lg_bgp($host,'2605',$pass,'show ip bgp neighbors '.$neighbor.' received-routes');	
			assign('router',$_GET['router']);
			if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/',$_GET['addr']))
				$command='show ip bgp neighbors '.$_GET['addr'].' received-routes';
			else
				$command='<font color="red">Please specify IP address</font>';
		}
		assign('command',$command);
		assign('ref','sibnr');
		assign('sibnr',$sibnr);
	break;
//}}}

//{{{ Show ip bgp x.x.x.x/xx
	case 'sib':
		$pass=getNodePwd($_GET['r'],$nodes);
		$sib=lg_bgp($_GET['r'],'2605',$pass,'show ip bgp '.$_GET['n']);
		$command='show ip bgp '.$_GET['n'];
		assign('command',$command);
		assign('router',$_GET['r']);
		assign('ref','sib');
		assign('sib',$sib);
	break;

	case 'bgp':
		if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/',$_GET['addr'])) {
			$pass=getNodePwd($_GET['router'],$nodes);
			$sib=lg_bgp($_GET['router'],'2605',$pass,'show ip bgp '.$_GET['addr']);	
			$command='show ip bgp '.$_GET['addr'];
		}
		else {
			$sib='';
			$command='<font color="red">Please specify IP address</font>';
		}
			assign('ref','sib');
			assign('sib',$sib);
			assign('router',$_GET['router']);
			assign('command',$command);
	break;

	case 'sibn':
		$pass=getNodePwd($_GET['r'],$nodes);
		$sibn=lg_bgp($_GET['r'],'2605',$pass,'show ip bgp neighbors '.$_GET['n']);	
		$command='show ip bgp neighbors '.$_GET['n'];
		assign('command',$command);
		assign('router',$_GET['r']);
		assign('ref','sibn');
		assign('sibn',$sibn);
	break;
}
//}}}

//{{{ display main template
echo display_template();
//}}}

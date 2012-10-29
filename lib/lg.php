<?
//{{{ functions
mysql_connect($mysql_host,$mysql_user,$mysql_password);
mysql_select_db($mysql_db);

// {{{ Remove multiple spaces,newlines,tabs
function rms($s) {
	$s = preg_replace('/\s+/', ' ',trim($s));
	return $s;
}
// }}}

// {{{ Get string between two strings
function get_string_between($string,$start,$end){
	$string=' '.$string;
	$ini=strpos($string,$start);
	if($ini==0) 
		return '';
	$ini+=strlen($start);
	$len=strpos($string,$end,$ini) - $ini;
	return substr($string,$ini,$len);
}
//}}}

//{{{ Get node password 
function getNodePwd($node,$all) {
	for ($i=0;$i<count($all);$i++) {
		if ($all[$i]['ip']==$node)
			$pass=$all[$i]['pass'];
	}
	return $pass;
}
//}}}

//}}}

//{{{ Initial setup
assign('html_title',$html_title);
assign('local_as',$local_as);
assign('nodes',$nodes);
assign('prefix_count',$prefix_count);
assign('command','');
assign('cmd',$_GET['cmd']);
assign('addr',$_GET['addr']);
assign('ref','');
assign('company_logo_type',$company_logo_type);
assign('company_logo_src',$company_logo_src);
//}}}

// {{{ show bgp information
function lg_bgp($host,$port,$pass,$cmd) {
	$whois=array('RIPE'=>'https://apps.db.ripe.net/search/query.html?searchtext=AS','ARIN'=>'http://whois.arin.net/rest/asn/AS','APNIC'=>'http://wq.apnic.net/apnic-bin/whois.pl?searchtext=AS');
	$readbuf='';
	$rnets=array();
	$link=fsockopen($host,$port,$errno,$errstr,5);
	if (!$link) {
		echo "cannot connect to router\n";
		return false;
	}
	socket_set_timeout($link,5);
	$password=$pass;
	$command=$cmd;
	fputs($link,"{$password}\nterminal length 0\n{$command}\n");
	fputs($link,"quit\n");
	while (!feof($link))
		$readbuf=$readbuf.fgets($link,512);

//show ip bgp IP/mask
	if (preg_match('/(show ip bgp) ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/',$command)) {
		$sib=get_string_between($readbuf,$cmd,'quit');
		if (preg_match('/Network not in table/',$sib)) {
			$sib=explode("\n",trim($sib));
			$sib=$sib[0];
			return $sib;
			exit;
		}
		$sib=explode("\n\r",$sib);
		$beg=explode("\n",$sib[0]);
		if (preg_match('/Not advertised/',$beg[3])) {
			for ($i=0;$i<4;$i++)
			$part1.=$beg[$i];
		}
		else {
			for ($i=0;$i<5;$i++)
			$part1.=$beg[$i];
		}
		preg_match('/(best) (#)([0-9]{1,})/',$part1,$m);
		$part1=preg_replace('/(best) (#)([0-9]{1,})/','<font color="red">$0</font>',$part1);
		$best=$m[3];
		if (preg_match('/Not advertised/',$beg[3]))
			$begex=explode("\n",$beg[4]);
		else
			$begex=explode("\n",$beg[5]);
		$line1=explode(',',trim($begex[0]));
		$asline=explode(' ',$line1[0]);
			for ($l=0;$l<count($asline);$l++) {	
				if (preg_match('/^[0-9]/',$asline[$l])) {
					$r=mysql_query("select * from autsys where asnum='AS{$asline[$l]}'");
					$res=mysql_fetch_assoc($r);
					$org=strtoupper($res['type']);
					$title='AS'.$asline[$l].'('.$org.')|'.$res['asname'];
					$asb.=' <a class="as2name" href="'.$whois[$org].$asline[$l].'" target="_blank" title="'.$title.'">'.$asline[$l].'</a>';
					if ($org=='ARIN')
						$asb.=' <a class="as2name" href="'.$whois[$org].$asline[$l].'/pft" target="_blank" title="'.$title.'">'.$asline[$l].'</a>';
				}
				else
					$asb.=$asline[$l].' ';
			}
		for ($b=1;$b<count($line1);$b++)
			$asb1.=$line1[$b];
		if ($asb1)
			$asb=$asb.','.$asb1;
		if (preg_match('/Not advertised/',$beg[3])) {
			$beg[4]=$asb.'<br>';
			for ($i=4;$i<count($beg);$i++)
				$part2.=$beg[$i];
		}
		else {
			$beg[5]=$asb.'<br>';
			for ($i=5;$i<count($beg);$i++)
				$part2.=$beg[$i];
		}
		if (preg_match('/multipath/',$part2)&&!preg_match('/best/',$part2))
			$part2='<font color="orange">'.$part2.'</font>';


		if ($best==1)
			$part2='<font color="red">'.$part2.'</font>';
		$part2.="\n\r";
		for ($i=1;$i<count($sib)-1;$i++) {
			$aslink='';
			$asln='';
			$mases='';
			$asl='';
			$aslink=explode("\n",$sib[$i]);	
			$asl=explode(',',trim($aslink[1]));
			$asl1=explode(' ',$asl[0]);
			for ($l=0;$l<count($asl1);$l++) {
				if (preg_match('/^[0-9]/',$asl1[$l])) {
					$r=mysql_query("select * from autsys where asnum='AS{$asl1[$l]}'");
					$res=mysql_fetch_assoc($r);
					$org=strtoupper($res['type']);
					$title='AS'.$asl1[$l].'('.$org.')|'.rms($res['asname']);
					$mases.=' <a class="as2name" href="'.$whois[$org].$asl1[$l].'" target="_blank" title="'.$title.'">'.$asl1[$l].'</a>';
					if ($org=='ARIN')
						$mases.=' <a class="as2name" href="'.$whois[$org].$asl1[$l].'/pft" target="_blank" title="'.$title.'">'.$asl1[$l].'</a>';
				
				}
				else
					$mases.=$asl1[$l].' ';

			}
			for ($j=1;$j<count($asl);$j++)
				$asl2.=$asl[$j];

			if($asl2)
				$asln.=$mases.','.$asl2.'<br>';
			else
				$asln.=$mases.'<br>';
					
			$sib[$i]=$aslink[0].$asln.$aslink[2].$aslink[3].$aslink[4].$aslink[5];
			if ($i==$best-1)
				$sib[$i]='<font color="red">'.$sib[$i].'</font>';
			if (preg_match('/multipath/',$sib[$i])&&!preg_match('/best/',$sib[$i]))
				$sib[$i]='<font color="orange">'.$sib[$i].'</font>';
			$part2.=$sib[$i]."\n\r";
		}
		$part2=preg_replace('/(from) ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', "$1 <a href=\"?cmd=sibn&n=$2&r={$host}\">$2</a>", $part2);
		$part2=preg_replace('/(, )(multipath)/','<font color="orange">$1 $2</font>',$part2);
		$sib=$part1."\n\r".$part2;
		return $sib;
		exit;
	}

//show ip bgp neighbors IP routes | received-routes | advertised-routes
	if (preg_match('/(show ip bgp neighbors) ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) (routes)/',$command)||preg_match('/(show ip bgp neighbors) ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) (received-routes)/',$command)||preg_match('/(show ip bgp neighbors) ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) (advertised-routes)/',$command)) {
		if (!get_string_between($readbuf,'Network','Total')) {
			$sibnr="Sorry! No routes available for this neighbor!";
			return $sibnr;
			exit;
		}
		$btable='BGP'.get_string_between($readbuf,'BGP','Network');
		$etable='Total'.get_string_between($readbuf,'Total',"\n");	
		$table='   Network'.get_string_between($readbuf,'Network','Total');
		$ttable='Network'.get_string_between($readbuf,'Network','Total');
		$table=explode("\n",$table);
		for ($i=1;$i<count($table);$i++) {
			$t1=explode('    ',$table[$i]);
			$cnt=count($t1)-1;
			$t2=explode(' ',trim($t1[$cnt]));
			for ($j=1;$j<count($t2)-1;$j++)
				$a.=$t2[$j]."\n";
		}
			
		$a=explode("\n",$a);
		for ($i=0;$i<count($a);$i++)
			$arr[$i]=$a[$i];
		$arr=array_values(array_unique($arr));
		for ($i=0;$i<count($arr);$i++) {
			$r=mysql_query("select * from autsys where asnum='AS{$arr[$i]}'");
			$res=mysql_fetch_assoc($r);
			$org=strtoupper($res['type']);
			$title='AS'.$arr[$i].'('.$org.')|'.$res['asname'];
			if (preg_match('/[0-9]/',trim($arr[$i]))) {
				$ttable=preg_replace("/({$arr[$i]})/",'<a class="as2name" href="'.$whois[$org].$arr[$i].'" target="_blank" title="'.$title.'">'.$arr[$i].'</a>',$ttable);
				if ($org=='ARIN')
					$ttable=preg_replace("/({$arr[$i]})/",'<a class="as2name" href="'.$whois[$org].$arr[$i].'/pft" target="_blank" title="'.$title.'">'.$arr[$i].'</a>',$ttable);
			}
		}
		$sibnr=$btable.$ttable.$etable;
		$sibnr=preg_replace("/(\*>|\*|\* |\*=) ([a-zA-z])([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\/[0-9]{1,2})/", "$1 $2<a href=\"?cmd=sib&n=$3&r={$host}\">$3</a>", $sibnr);
		$sibnr=preg_replace("/(\*>i|\*=i)([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\/[0-9]{1,2})/", "$1<a href=\"?cmd=sib&n=$2&r={$host}\">$2</a>", $sibnr);
		$sibnr=preg_replace("/(\*>i|\*=i)([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/", "$1<a href=\"?cmd=sib&n=$2&r={$host}\">$2</a>", $sibnr);
		$sibnr=preg_replace("/(\*>|\*|\* |\*=) ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\/[0-9]{1,2})/", "$1 <a href=\"?cmd=sib&n=$2&r={$host}\">$2</a>", $sibnr);
		$sibnr=preg_replace("/(\*>|\* |\*=) ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/", "$1 <a href=\"?cmd=sib&n=$2&r={$host}\">$2</a>", $sibnr);
		$sibnr=preg_replace("/(\*>|\* |\*=) ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\/[0-9]{1,2})/", "$1 <a href=\"?cmd=sib&n=$2&r{$host}\">$2</a>", $sibnr);
		return $sibnr;
		exit;
	}

//show ip bgp neigbors x.x.x.x/x
	if (preg_match('/(show ip bgp neighbors) ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/',$command)) {
		$nsib='';
		$sibn=" BGP".get_string_between($readbuf,'BGP','#');
		$sibn=explode("\n",$sibn);
		for ($i=0;$i<count($sibn)-1;$i++)
			$nsib.=$sibn[$i];
		$sibn='BGP'.preg_replace("/(BGP neighbor is) ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/", "neighbor is <a href=\"?cmd=ping&ip=$2\" target=\"_blank\">$2</a>", $nsib);
		return $sibn;
		exit;
	}

//show ip bgp summary
	if ($cmd=='show ip bgp summary') {
		$ret=get_string_between($readbuf,'summary','#');
		$ret1=get_string_between($ret,'BGP','Neighbor');
		$ret2=get_string_between($readbuf,'Total','#');
		$tot=explode("\n",$ret2);
		preg_match("/(local AS number) ([0-9]{5,})/",$ret1,$m);
		$r=mysql_query("select * from autsys where asnum='AS{$m[2]}'");
		$res=mysql_fetch_assoc($r);
		$org=strtoupper($res['type']);
		$title='AS'.$m[2].'('.$org.')|'.$res['asname'];
		$bgpinfo[0]='BGP'.preg_replace("/(local AS number) ([0-9]{5,})/", " local AS number <a class=\"as2name\" href=\"{$whois[$org]}$2\" target=\"_blank\" title=\"{$title}\">$2</a>", $ret1);
		if ($org=='ARIN')
			$bgpinfo[0]='BGP'.preg_replace("/(local AS number) ([0-9]{5,})/", " local AS number <a class=\"as2name\" href=\"{$whois[$org]}$2/pft\" target=\"_blank\" title=\"{$title}\">$2</a>", $ret1);
		$bgpinfo[1]='Total '.$tot[0];
		$ret=get_string_between($readbuf,'PfxRcd','Total');
		$ret1=explode("\n",$ret);
		$ret=array();
		for ($j=0;$j<count($ret1);$j++) {
				$line='';
			if (preg_match('/[a-z]|[A-Z]|[0-9]/',$ret1[$j])) {
				if (preg_match('/                                /',$ret1[$j])) {
					$desc=preg_replace('/ /','_',preg_replace('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}    /','',$ret1[$j-1]));
					$bip=preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/',$ret1[$j-1],$m);
					$part1=$m[0].' '.$desc;
					$line=$part1.' '.preg_replace("/                                /",' ',$ret1[$j]);
				}
				else {	
					$bip=preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/',$ret1[$j],$m);
					$dd=get_string_between($ret1[$j],$m[0],' 4');
					if (!preg_match('/[a-zA-Z0-9]/',$dd))
						$desc='nodescr';
					else
						$desc=$dd;
					$tr=explode($m[0],$ret1[$j]);
					$rest=trim($tr[1]);
					$td=str_replace("{$desc}",'',$tr[1]);
					if (preg_match('/^4/',trim($td)))
						$line=$m[0]." ".preg_replace('/ /','_',trim($desc))." ".$td;
				
				}
				$ret[]=trim($line);

			}
		}
		for ($i=0;$i<count($ret);$i++) {
			$bgpi=explode(" ",preg_replace('/\s+/',' ',$ret[$i]));
			$bgpinfo[$i+2]['neighbor']=$bgpi[0];
			$bgpinfo[$i+2]['description']=$bgpi[1];
			$bgpinfo[$i+2]['version']=$bgpi[2];
			$bgpinfo[$i+2]['as']=$bgpi[3];
			$bgpinfo[$i+2]['msgrcvd']=$bgpi[4];
			$bgpinfo[$i+2]['msgsent']=$bgpi[5];
			$bgpinfo[$i+2]['tblver']=$bgpi[6];
			$bgpinfo[$i+2]['inq']=$bgpi[7];
			$bgpinfo[$i+2]['outq']=$bgpi[8];
			$bgpinfo[$i+2]['uptime']=$bgpi[9];
			if ($bgpi[10]=='Idle')
				$bgpinfo[$i+2]['state']=$bgpi[10]." ".$bgpi[11];
			else
				$bgpinfo[$i+2]['state']=$bgpi[10];
			$r=mysql_query("select * from autsys where asnum='AS{$bgpi[3]}'");
			$res=mysql_fetch_assoc($r);
			$bgpinfo[$i+2]['title']='AS'.$bgpi[3].'('.strtoupper($res['type']).')|'.$res['asname'];
			
		}
		return $bgpinfo;
		exit;
	}
}
//}}}

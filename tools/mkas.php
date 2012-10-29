<?
include ('/var/www/imon/functions.php');
$adata='';
$data='';
$as=array();
//$fh=fopen('ftp://ftp.lacnic.net/pub/stats/lacnic/delegated-lacnic-latest','r');
//$fh=fopen('ftp://ftp.afrinic.net/pub/stats/afrinic/delegated-afrinic-latest','r');
//ARIN
$fh=fopen('ftp://ftp.arin.net/info/asn.txt','r');
while (!feof($fh))
	$adata.=fread($fh,8192);
fclose($fh);
$arin=explode('POC Handles',$adata);
$asarin=explode("\n",$arin[1]);
for ($i=1;$i<count($asarin);$i++) {
	$mkas=explode(' ',rms($asarin[$i]));
	$as['AS'.$mkas[0]]['as-name']=trim($mkas[1]);
	$as['AS'.$mkas[0]]['type']='arin';
}
//RIPE
$data='';
$fh=fopen('compress.zlib://ftp://ftp.ripe.net/ripe/dbase/split/ripe.db.aut-num.gz','r');
while (!feof($fh))
	$data.=fread($fh,8192);
fclose($fh);
$ripe=explode("aut-num:",$data);
$ex='';
for ($i=0;$i<count($ripe);$i++) {
	$ex=explode("\n",$ripe[$i]);
	$autnum=$ex[0];
	for ($ii=0;$ii<count($ex);$ii++) {
		if (preg_match('/^as-name:/',$ex[$ii])) {
			$a=explode(' ',rms($ex[$ii]));
			$asnumber=trim($ex[0]);
			$as[$asnumber]['as-name']=trim($ex[1]);
			$as[$asnumber]['type']='ripe';
		}
	}
	
}
//APNIC
$data='';
$fh=fopen('compress.zlib://ftp://ftp.apnic.net/pub/whois-data/APNIC/split/apnic.db.aut-num.gz','r');
while (!feof($fh))
	$data.=fread($fh,8192);
fclose($fh);
$ripe=explode("aut-num:",$data);
$ex='';
for ($i=0;$i<count($ripe);$i++) {
	$ex=explode("\n",$ripe[$i]);
	$autnum=$ex[0];
	for ($ii=0;$ii<count($ex);$ii++) {
		if (preg_match('/^as-name:/',$ex[$ii])) {
			$a=explode(' ',rms($ex[$ii]));
			$asnumber=trim($ex[0]);
			$as[$asnumber]['as-name']=trim($ex[1]);
			$as[$asnumber]['type']='apnic';
		}
	}
	
}
//JPNIC
$data='';
$fh=fopen('compress.zlib://ftp://ftp.apnic.net/pub/whois-data/JPNIC/split/jpnic.db.aut-num.gz','r');
while (!feof($fh))
	$data.=fread($fh,8192);
fclose($fh);
$ripe=explode("aut-num:",$data);
$ex='';
for ($i=0;$i<count($ripe);$i++) {
	$ex=explode("\n",$ripe[$i]);
	$autnum=$ex[0];
	for ($ii=0;$ii<count($ex);$ii++) {
		if (preg_match('/^as-name:/',$ex[$ii])) {
			$a=explode(' ',rms($ex[$ii]));
			$asnumber=trim($ex[0]);
			$as[$asnumber]['as-name']=trim($ex[1]);
			$as[$asnumber]['type']='jpnic';
		}
	}
	
}
$asdb=serialize($as);
$fh=fopen('as.db','w');
fwrite($fh,$asdb);
fclose($fh);
$data='';
$fh=fopen('as.db','r');
while (!feof($fh))
	$data.=fread($fh,8192);
fclose($fh);
$data=unserialize($data);
mysql_connect('localhost','root','');
mysql_select_db('lg');
mysql_query("truncate autsys");
	foreach($data as $k=>$v) {
		$autnum=$k;
		$type=$data[$k]['type'];
		$asname=str_replace(array('as-name:         ','as-name:        '),'',$data[$k]['as-name'])."\n";
		mysql_query("insert into autsys values (NULL,'{$autnum}','{$asname}','{$type}')");
		echo mysql_error();
	}
mysql_close();
		
?>

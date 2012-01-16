<?php


//header('Content-Type: text/plain');
//var_export($_SERVER);
//exit;


require_once 'DjinnIT_WebDAV_Server.php';


class My_WebDAV_Server extends DAV_Server 
{

  
protected function user_GET(&$headers) {
  $stream = fopen('/home/pieterb/tmp/luijf.pem', 'r');
  #$stream = fopen('/home/pieterb/tmp/luijf.pem', 'r');
  #$stat = fstat($stream);
  $headers['Content-Type'] = 'text/plain; charset=ASCII';
  #$headers['Content-Length'] = $stat['size'];
  return $stream;
}


protected function user_PROPFIND($depth, $props) {
  $retval = array();
  $this->treatAsCollection();
  $p = new DAV_Props();
  $p->setProperty(DAV_Props::DAV_CREATIONDATE)
    ->setresourcetype(TRUE)
    ->setProperty(DAV_Props::DAV_GETCONTENTLENGTH, 10)
    ->setProperty(DAV_Props::DAV_GETCONTENTTYPE, 'text/plain')
    ->setProperty('http://www.sara.nl/web%20dav/ GSIstuff', 'DN: dada!');
  $p->setStatus(DAV_Props::DAV_CREATIONDATE, '403 Go away!');
  $retval[$this->pathInfo()] = $p;
  return $retval;
}

  
} // class My_WebDAV_Server

$server = new My_WebDAV_Server();
$server->serveRequest();

?>

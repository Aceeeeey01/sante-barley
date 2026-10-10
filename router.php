<?php
// Local PHP development router; mirrors Apache .htaccess restrictions.
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'/');
if(preg_match('#(^|/)(\.|config(?:/|$)|database(?:/|$)|scripts(?:/|$)|includes(?:/|$)|var(?:/|$))#',$path)||str_contains($path,'..')||preg_match('/\.(md|sql|json|log)$/i',$path)){http_response_code(404);exit('Not found.');}
return false;

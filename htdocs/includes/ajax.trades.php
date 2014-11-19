<?php
chdir('..');
include '../cfg/cfg.php';

$currency1 = ($_REQUEST['currency'] != 'All') ? ereg_replace("/[^\da-z]/i", "",$_REQUEST['currency']) : false;
$notrades = $_REQUEST['notrades'];
$limit = $_REQUEST['get10'] ? 10 : 5;
$user = $_REQUEST['user'];
$currency_info = $CFG->currencies[strtoupper($currency1)];


if (!$notrades) {
	API::add('Transactions','get',array(false,false,5,$currency1));
	API::add('Stats','getBTCTraded');
}
elseif (!$_REQUEST['get10']) {
	$limit = false;
}

if ($_REQUEST['last_price']) {
	API::add('Transactions','get',array(false,false,1,$currency1));
	
	if ($currency1)
		API::add('User','getAvailable');
}

API::add('Orders','get',array(false,false,$limit,$currency1,$user,false,1));
API::add('Orders','get',array(false,false,$limit,$currency1,$user,false,false,false,1));
$query = API::send();

$return['asks'][] = $query['Orders']['get']['results'][1];
$return['bids'][] = $query['Orders']['get']['results'][0];

if (!$notrades) {
	$return['transactions'][] = $query['Transactions']['get']['results'][0];
	$return['btc_traded'] = $query['Stats']['getBTCTraded']['results'][0];
}

if ($_REQUEST['last_price']) {
	$return['last_price'] = $query['Transactions']['get']['results'][0][0]['btc_price'];
	$return['last_price_curr'] = $last_trans_currency = (strtolower($query['Transactions']['get']['results'][0][0]['currency']) == $currency1) ? '' : ((strtolower($query['Transactions']['get']['results'][0][0]['currency1']) == $currency1) ? '' : ' ('.$query['Transactions']['get']['results'][0][0]['currency1'].')');
	$return['fa_symbol'] = $query['Transactions']['get']['results'][0][0]['fa_symbol'];
	$return['last_trans_color'] = ($query['Transactions']['get']['results'][0][0]['maker_type'] == 'sell') ? 'price-green' : 'price-red';
	
	if ($currency1) {
		$return['available_fiat'] = number_format($query['User']['getAvailable']['results'][0][strtoupper($currency1)],2);
		$return['available_btc'] = number_format($query['User']['getAvailable']['results'][0]['BTC'],8);
	}
}

echo json_encode($return);
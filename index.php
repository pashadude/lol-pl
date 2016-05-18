<?php  

include 'leaguewrap-master/vendor/autoload.php';
use \LeagueWrap\Api;


$myKey    = "....put your key here"; 

function getPlayerStats ($nickname,$region,$key,$mode){
	$api = new Api($key);
	$api->setRegion($region);
	$summoner = $api->summoner();    
	$data = $summoner->info($nickname);
	$result['lvl'] = $data->summonerLevel;
	$result['id'] = intval($data->id); 

	if ($mode == 'ranked') {
		$stats = $api->stats()->ranked($result['id']);
	} elseif ($mode == 'every') {
		$stats = $api->stats()->summary($result['id']);
	} else {
		throw new Exception('No such mode');
	}
	
	
	$division = $api->league()->league($result['id'], true);
	print_r($division);

}

function getLatestMatches ($nickname, $region, $key, $matchNumber){
	$api = new Api($key);
	$api->setRegion($region);
	$summoner = $api->summoner();    
	$data = $summoner->info($nickname);
	$matches = $api->matchHistory()->history(intval($data->id));
	for ($i=0; $i < $matchNumber; $i++) { 
		print_r($matches->match($i));
	}

}

function getGamez ($id,$region,$key) {
	$api = new Api($key);
	$api->setRegion($region);
	$games = $api->game();
	$all = $games->recent($id);		
	foreach ($all as $game) {
		print_r($game->get('createDate'));
		echo "  ";
		print_r($game->get('fellowPlayers'));
		echo ";";
	/*
		foreach ($players as $player) {			
			if ($player->get('teamId')=='100') {
				$participants['team_1'] = $player->get('summonerId');
			} else {
				$participants['team_2'] = $player->get('summonerId');
			}
		}
		print_r($participants);*/
	}
}



function CheckMatch ($match) {
	$found = false;
	$unchecked = array();
	$k = count ($match['payload']['teams']['team_1']);
	for ($i=0; $i < $k; $i++) { 
		$unchecked[$i] = $match['payload']['teams']['team_1'][$i];
		$unchecked[$i+$k] = $match['payload']['teams']['team_2'][$i];
	}
	while (!$found) {
		$k = count($unchecked);
        $r = mt_rand(0,$k - 1);
		//print_r($unchecked);
		$key = "... put your key here"; 
		$api = new Api($key);
		$api->setRegion('eune');
		$games = $api->game();
		$j = intval($unchecked[$r]);
		print_r($unchecked[$r]);
		echo " ";
		$gamez = $games->recent($j);
		foreach ($gamez as $game) {
			$date = $game->get('createDate');
			$players = $game->get('fellowPlayers');
			$stats = $game->get('stats');
			$win = $stats->get('win');		
			$participants = array();
			foreach ($players as $player) {			
				if ($player->get('teamId')=='100') {
					$participants['team_1'][] = $player->get('summonerId');
				} else {
					$participants['team_2'][] = $player->get('summonerId');
				}
			}
			if (count($participants['team_1'])>count($participants['team_2'])) {
				$participants['team_2'][count($participants['team_2'])] = $unchecked[$r];
				$team = 'team_2';
				$nonteam = 'team_1';
			} else {
				$participants['team_1'][count($participants['team_1'])] = $unchecked[$r];
				$team = 'team_1';
				$nonteam = 'team_2';
			}
			print_r($participants);
			echo " ";
			if ($match['payload']['time_period']<= abs($match['payload']['timestamp']-$date)){
				if (
						(
							(array_diff($participants['team_1'], $match['payload']['teams']['team_1']) == null)&&
							(array_diff($participants['team_2'], $match['payload']['teams']['team_2']) == null)
						)||(
							(array_diff($participants['team_1'], $match['payload']['teams']['team_2']) == null)&&
							(array_diff($participants['team_2'], $match['payload']['teams']['team_1']) == null)
						)
					){
						$found = true;
						$results['desc'] = 'game was found';
						if ($win == null) {
							$results['winner'] = $participants[$nonteam];
							$results['loser'] = $participants[$team];							
						} else {
							$results['winner'] = $participants[$team];
							$results['loser'] = $participants[$nonteam];
						}			
				}
			}
		}
		
		unset($unchecked[$r]);		
		if (count ($unchecked) == 0) {
			$results['desc'] = 'no game was found';
			$found = true;
		}
	}
	return $results;
}

//getPlayerStats('pashadude','eune',$myKey,'every');
//47254993 - antom
//30406171 -ya
//getGamez(41106785,'eune',$myKey);


$match = array (
	'payload' => array (
		'timestamp' => 1405617878194,
		'time_period' => 0,
		'teams' => array(
			'team_1'=> array (
				47254993
				//41106785
			),
			'team_2'=> array (
				30406171
				//41106785
			)
		)	
	)

);



$wrongmatch = array (
'payload' => array (
		'timestamp' => 1405617878194,
		'time_period' => 0,
		'teams' => array(
			'team_1'=> array (
				47254993
				//41106785
			),
			'team_2'=> array (
				//30406171
				41106785
			)
		)	
	)
);

$bigmatch = array (
'payload' => array (
		'timestamp' => 1414228535830,
		'time_period' => 0,
		'teams' => array(
			'team_1'=> array (
				40018620,
				35509074,
				43173231,
				37205663,
				41106785		
				//41106785
			),
			'team_2'=> array (
				28537477,
				37945081,
				40049149,
				37686550,
				33978502
			)
		)	
	)
);



//print_r(CheckMatch($match));

//print_r(CheckMatch($wrongmatch));

print_r(CheckMatch($bigmatch));

/*
$api = new Api($myKey);
$summoner = $api->summoner();
$me = $summoner->info(47254993);
$game = $api->game();
$game->recent($me);
var_dump($me->recentGame(0)); */

//getLatestMatches('pashadude','eune', $myKey, 5);

?>

 

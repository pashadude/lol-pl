<?php  

include 'leaguewrap-master/vendor/autoload.php';
use \LeagueWrap\Api;


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
		
		$key = "907b1649-4946-43bf-afb7-90456bd917fb"; 
		$api = new Api($key);
		$api->setRegion('eune');
		$games = $api->game();
		$j = intval($unchecked[$r]);
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
					return $results;
				}
			}

		}

		unset($unchecked[$r]);
		
		if (count ($unchecked) == 0) {
			$results['desc'] = 'no game was found';
			$found = true;
		}
	}	
}

$match = array (
	'payload' => array (
		'timestamp' => 1405617878194 ,
		'time_period' => 0,
		'teams' => array(
			'team_1'=> array (
				30406171
			),
			'team_2'=> array (
				
				47254993
			)
		)	
	)

);

print_r(CheckMatch($match));
?>

 
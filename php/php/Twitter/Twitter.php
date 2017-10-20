<?php
	require_once('twitteroauth.php');

	define('TWEET_LIMIT', 20);
	define('CONSUMER_KEY', 'Ap94AupI1JUjRwj9d9pNQ');
	define('CONSUMER_SECRET', 'kl75t2XASQKfoUXJ7tGu4bdynmt2WG9dJFacufDr0M0');
	define('ACCESS_TOKEN', '15968208-2Lhby2xghLpqWk7gs1pdqqrV7GvN0HtmUTfi2EdRi');
	define('ACCESS_TOKEN_SECRET', 'ESeksfES56BHxfCQEPFomecpbzFAEYtbSZe7BnLw9T8RV');

	class Twitter {
		function loadTweets($account) {
			$twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
		
			$tweets = $twitter->get('statuses/user_timeline', 
									array('screen_name' => $account, 
											'exclude_replies' => 'true', 
											'include_rts' => 'true', 
											'count' => TWEET_LIMIT));
										
			if (!empty($tweets)) {
				$processed_tweets = array();
		
				foreach($tweets as $tweet) {
					// Si es un retweet
					if (property_exists($tweet, 'retweeted_status'))
						$tweet = $tweet->retweeted_status;
				
					$text = $this->processTweetText($tweet);
				
					$date = date("j M Y", strtotime($tweet->created_at));

					$username = $tweet->user->name;
					$user_account =  $tweet->user->screen_name;
					$account_image = $tweet->user->profile_image_url;
					$language = $tweet->lang;
				
					$tweet = array(
						'text' => $text,
						'date' => $date,
						'language' => $language,
						'user' => array(
							'name' => $username,
							'account' => $user_account,
							'image' => $account_image
						)
					);
				
					array_push($processed_tweets, $tweet);
				}
			
				return $processed_tweets;
			}
										
			return null;
		}
	
		function processTweetText($tweet) {
			$replace_index = array();
		
			$text = $tweet->text;
		
			if (property_exists($tweet, 'entities') ) {
				foreach ($tweet->entities as $area => $items) {
				
					foreach ($items as $item) {
						$entity = $this->processEntity($area, $item);
						$href = $entity['href'];
						$string = $entity['string'];
						$prefix = $entity['prefijo'];
					
						if (!(strpos($href, 'http://') === 0)) 
							$href = "http://".$href;
						
						$index = substr($text, $item->indices[0], $item->indices[1]-$item->indices[0]);
						$replace = "<a class=\"enlace\" href=\"$href\">{$prefix}{$string}</a>";
						$replace_index[$index] = $replace;
					}
				}
			
				foreach ($replace_index as $replace => $with) 
					$text = str_replace($replace, $with, $text);
			}
		
			return $text;
		}
	
		function processEntity($area, $item) {
			$href = '';
			$string = '';
		
			switch ( $area ) {
				case 'hashtags':
					$prefix = '#';
					$url = 'http://twitter.com/search/?src=hash&q=%23';
					$string = $item->text;
					$href = $url.$string;
					break;
				case 'user_mentions':
					$prefix = '@';
					$url = 'http://twitter.com/';
					$string = $item->screen_name;
					$href = $url.$string;
					break;
				case 'media': 
				case 'urls':
					$prefix = '';
					$url = '';
					$string = $item->display_url;
					$href = $item->expanded_url;
					break;
				default: 
					break;
			}
		
			return array('href' => $href, 'string' => $string, 'prefijo' => $prefix);
		}
	}
?>
<?php
	require 'php/Twitter/Twitter.php';
	require 'php/Mendeley/papers.php';
	
	// Mustache loader
	require 'php/Mustache/Autoloader.php';
	Mustache_Autoloader::register();

	// Create Mustache engine
	$mustache = new Mustache_Engine(array(
   		'loader' => new Mustache_Loader_FilesystemLoader('views')
	));

	// Read data from JSON file
	$data = json_decode(file_get_contents('data/data.json'), true);
	
	// Add current language
	$language = isset($_GET['language']) ? $_GET['language'] : '';
	
	if (empty($language) && isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
		$language =  substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	}
	
	if (empty($language))
		$language = 'en';
	
	$data['language_' . $language] = 'true';
	
	// Set selected language
	for ($i = 0; $i < count($data['languages']); $i++) {
		if ($language == $data['languages'][$i]['code'])
			$data['languages'][$i]['selected'] = 'true';
	}
	
	// Load selected language labels
	$labels = json_decode(file_get_contents('static/lang/' . $language . '.json'), true);
	$data['labels'] = $labels;

	// Load Twitter tweets
	$tweets = (new Twitter)->loadTweets($data['twitter-account']);
	$data['tweets'] = $tweets;
	
	// Load Mendeley papers
	$papers = (new Papers)->getPapers($data['mendeley']); 
	$data['papers'] = $papers;

	// Read view template
	$template = $mustache->loadTemplate('index');
	
	echo $template->render($data);
?>
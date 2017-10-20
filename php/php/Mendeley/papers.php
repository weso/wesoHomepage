<?php
	require 'Mendeley.php';
	
	class Papers {
		var $file_mendeley = "tmp/mendeley.json";

		function getPapers($account) {
			$remote_connection = false;
		
			try {
				if (!file_exists($this->file_mendeley))
					$remote_connection = true;
				else {
					$json = file_get_contents($this->file_mendeley);
					$documents = json_decode($json, true);
				
					//if (!array_key_exists('date', $documents) || $documents['date'] != date('Y-m-d'))
					//	$remote_connection = true;
				}
				
				if ($remote_connection) {
					$json = (new Mendeley())->fetch_docs_in_group($account);
					$documents = json_decode($json, true);
				}
			
				$publications = array();
			
				if (!empty($documents)) {
					foreach ($documents['documents'] as $document) {
						if (array_key_exists('year', $document))
							$year = $document['year'];
						else 
							$year = '';
					
						if (empty($publications[$year]))
							$publications[$year] = array('year' => $year,  'documents' => array());
						
						$author_list = '';
						
						foreach ($document['authors'] as $author) {
							if ($author_list != '')
								$author_list = $author_list . ', ';
								
							$author_list = $author_list . $author['forename'] . ' ' . $author['surname'];
						}
						
						$document['author_list'] = $author_list;
						
						array_push($publications[$year]['documents'], $document);
					}
				
					krsort($publications);
				}
				
				$aux = array();
				foreach ($publications as $year => $element)
					array_push($aux, $element);
				
				$publications = $aux;
			}
			catch(Exception $e) {
				$publications = array();
			}
		
			if ($remote_connection) {
				$documents['date'] = date('Y-m-d');
			
				file_put_contents($this->file_mendeley, json_encode($documents));
			}
		
			return $publications;
		}
	}
?>

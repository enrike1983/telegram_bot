<?php

namespace Telegram;

use Telegram\Crawler;
use Telegram\Config;

require __DIR__ . '/vendor/autoload.php';
include 'config.php';
include 'Crawler.php';

	class Bot {

        const SAY_COMMAND = '/say';

		protected $updates_api = 'https://api.telegram.org/bot%s/getUpdates%s';
		protected $send_message_api = 'https://api.telegram.org/bot%s/sendMessage';
        protected $filename = 'data';

		protected function httpGet($url)
		{
		    $ch = curl_init();  
		 
		    curl_setopt($ch,CURLOPT_URL,$url);
		    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		 
		    $output = curl_exec($ch);
		 
		    curl_close($ch);
		    return $output;
		}

		public function httpPost($url,$params)
		{
		  $postData = '';
		   //create name value pairs seperated by &
		   foreach($params as $k => $v) 
		   { 
		      $postData .= $k . '='.$v.'&'; 
		   }
		   rtrim($postData, '&');
		 
		    $ch = curl_init();  
		 
		    curl_setopt($ch,CURLOPT_URL,$url);
		    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		    curl_setopt($ch,CURLOPT_HEADER, false); 
		    curl_setopt($ch, CURLOPT_POST, count($postData));
		        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    
		 
		    $output=curl_exec($ch);
		 
		    curl_close($ch);
		    return $output;
		 
		}

        public function getToken()
        {
            return Config::TOKEN;
        }

		public function getOffset()
		{
			//prende ultimo valore in db +1
			if($res = file_get_contents($this->filename)) {
				return (int) $res + 1;	
			} else {
				return '';
			}
		}

		protected function setOffset($offset)
		{
			//setta ultimo valore prcessato e mette in db
			$myfile = fopen($this->filename, "w") or die("Unable to open file!");
			fwrite($myfile, $offset);
		}

		public function process()
		{
			$offset = $this->getOffset();
			if($offset) {
				$offset = '?offset='.$offset;
			}

            try {
                //$result = $this->httpGet(sprintf($this->updates_api, $this->getToken(), $offset));
                $array_res = json_decode($_POST, true);

                if($array_res['ok']) {
                    //qui la mia logica applicativa
                    foreach($array_res['result'] as $el) {

                        $command = substr($el['message']['text'], 0, strpos($el['message']['text'], ' '));

                        switch($command) {
                            case self::SAY_COMMAND:
                                $this->say($el);
                        }
                    }
                    if(isset($el['update_id']))
                        $this->setOffset($el['update_id']);
                }
            } catch(\Exception $e) {
                echo 'E\' successo qualcosa di brutto!';
            }
		}

        protected function say($el)
        {
            if($text = str_replace('/say', '', $el['message']['text'])) {

                //$cinemas = Crawler::findCinemas('http://www.google.it/movies?near='.urlencode($text));
                $cinemas = array('uno', 'due', 'tre');

                //creazione tastiera
                $content = array(
                    'chat_id' => $el['message']['chat']['id'],
                    'reply_markup' => json_encode(array(
                        'keyboard' => array(
                            $cinemas
                        )
                    )),
                    'text' => "Ecco i cinema di ".$text
                );

                //messaggio standard
                /*$content = array(
                    'chat_id' => $el['message']['chat']['id'],
                    'text' => $text
                );  */

                $res = $this->httpPost(sprintf($this->send_message_api, $this->getToken()),
                    $content
                );
            }
        }
	}

	$tb = new Bot();

	$result = $tb->process();

?>
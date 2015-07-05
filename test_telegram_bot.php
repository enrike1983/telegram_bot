<?php

namespace Telegram;

use Telegram\Crawler;
use Telegram\Config;

require __DIR__ . '/vendor/autoload.php';
include 'config.php';
include 'Crawler.php';

	class Bot {

        const SAY_COMMAND = '/say';

		protected $send_message_api = 'https://api.telegram.org/bot%s/sendMessage';

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

		public function process()
		{
            try {
                
                $message = json_decode(file_get_contents('php://input'), true);
                // if it's not a valid JSON return
                if(is_null($message)) return;

$myfile = fopen("data", "w") or die("Unable to open file!");
fwrite($myfile, var_dump($message));
fclose($myfile);
die();

                $command = substr($message['message']['text'], 0, strpos($message['message']['text'], ' '));

                switch($command) {
                    case self::SAY_COMMAND:
                        $this->say($message);
                }


            } catch(\Exception $e) {
                echo 'E\' successo qualcosa di brutto!';
            }
		}

        protected function say($el)
        {
            if($text = str_replace('/say', '', $el['message']['text'])) {

                $cinemas = Crawler::findCinemas('http://www.google.it/movies?near='.urlencode($text));

                //creazione tastiera
                $content = array(
                    'chat_id' => $el['message']['chat']['id'],
                    'reply_markup' => json_encode(array(
                        'keyboard' => $cinemas
                    )),
                    'text' => "Ecco i cinema di".$text
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
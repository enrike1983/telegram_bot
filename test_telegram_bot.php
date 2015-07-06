<?php

namespace Telegram;

use Telegram\Crawler;
use Telegram\Config;

require __DIR__ . '/vendor/autoload.php';
include 'config.php';
include 'Crawler.php';

	class Bot {

        const SAY_COMMAND = '/say';
        const GET_PROGRAMMAZIONE_COMMAND = '/programmazione';

        protected $google_movies_endpoint = 'http://www.google.it/movies';

        protected $base_api = 'https://api.telegram.org/bot%s';
		protected $send_message_api = '/sendMessage';
        protected $send_chat_action_api = '/sendChatAction';

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

                //chat ID
                $chat_id = $message["message"]["chat"]["id"];
                $command = substr($message['message']['text'], 0, strpos($message['message']['text'], ' '));

                // show to the client that the bot is typing
                file_get_contents($this->base_api.$this->send_chat_action_api."?chat_id=".$chat_id."&action=typing");

                //main router for commands
                switch($command) {
                    case self::SAY_COMMAND:
                        $this->say($message);
                        break;
                    case self::GET_PROGRAMMAZIONE_COMMAND:
                        $this->movies($message);
                        break;
                }
            } catch(\Exception $e) {
                echo 'Something bad happened';
            }
		}

        protected function say($el)
        {
            if($text = str_replace('/say', '', $el['message']['text'])) {

                $cinemas = Crawler::findCinemas($this->google_movies_endpoint.'?near='.urlencode($text));

                //creazione tastiera
                $content = array(
                    'chat_id' => $el['message']['chat']['id'],
                    'reply_markup' => json_encode(array(
                        'keyboard' => $cinemas
                    )),
                    'text' => "Ecco i cinema di".$text
                );

                $res = $this->httpPost(sprintf($this->base_api.$this->send_message_api, $this->getToken()),
                    $content
                );
            }
        }

        protected function movies($el)
        {
            //reply TEST
            $content = array(
                'chat_id' => $el['message']['chat']['id'],
                'text' => "Ti verrà inviata la programmazione!"
            );

            $res = $this->httpPost(sprintf($this->base_api.$this->send_message_api, $this->getToken()),
                $content
            );
        }
	}

	$tb = new Bot();

	$result = $tb->process();

?>
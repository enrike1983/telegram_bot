<?php

namespace Telegram;

use Sunra\PhpSimple\HtmlDomParser;
use Telegram\Bot;

class Crawler
{
    public static function findCinemas($str)
    {
        $res = array();
        $dom = HtmlDomParser::file_get_html( $str );

        $cinemas = $dom->find( ".movie_results");
        foreach($cinemas as $cinema) {
            foreach($cinema->children() as $theater){
                foreach($theater->find('.desc') as $els) {
                    foreach($els->find('h2') as $title) {
                        $res[] = array(Bot::GET_PROGRAMMAZIONE_COMMAND.' '.$title->text());
                    }
                }
                //$res
            }
        }
        return $res;
    }

    public static function findMovies($str, $cinema_name)
    {
        $res = array();
        $dom = HtmlDomParser::file_get_html( $str );

        $cinemas = $dom->find( ".movie_results");
        foreach($cinemas as $cinema) {
            foreach($cinema->children() as $theater){
                foreach($theater->find('.desc') as $els) {
                    foreach($els->find('h2') as $title) {
                        if(strtolower($title->text()) == $cinema_name) {
                            foreach($theater->find('.name') as $name) {
                                $res[] = array($name->text());
                            }
                        }
                    }
                }
            }
        }

        return $res;
    }
}
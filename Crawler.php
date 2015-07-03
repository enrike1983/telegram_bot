<?php

namespace Telegram;

use Sunra\PhpSimple\HtmlDomParser;

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
                        $res[] = array($title->text());
                    }
                }
                //$res
            }
        }
        return $res;
    }
}
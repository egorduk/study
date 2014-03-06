<?php

namespace Acme\RssBundle\Helper;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class for filtering and building cloud tags from input string
 * @author Egor Dyukarev <edyukarev@itransition.com>
 */
class Cloud {

    /**
     * The counter for output tags in cloud
     * @var int
     * @access protected
     */
    protected $countFilterWord = 0;

    /**
     * A function to filtering the string
     * @param string $str the original string with unions and etc
     * @return string
     */
    public function filterStr($str)
    {
        $arrayDeleteWord = array(' in ',
            ' the ',
            ' a ',
            ' and ',
            ' or ',
            ' on ',
            ' no ',
            ' not ',
            ' of ',
            ' at ' ,
            ' an ',
            ' to ',
            ' by ',
            ' with ',
            ' is ',
            ' are ',
            ' as ',
            ' its ',
            ' us ',
            ' we ',
            ' he ',
            ' she ',
            ' all ',
            ' have ',
            ' has ',
            ' but ',
            ' more ',
            ' for ',
            ' up ',
            '$',
            ' it ',
            '&',
            'be',
            'the ',
            ' his ');

        $arrayDeletePunct = array(' - ', ',', '.', ':', ' -- ', ' ... ', '!', ';', '?');

        //$str = preg_replace("/[[:punct:]]/", " ", $str);
        $str = preg_replace("/[[:digit:]^]/", " ", $str);
        $str = str_replace($arrayDeletePunct, " ", $str);
        $str = str_ireplace($arrayDeleteWord, " ", $str);
        $str = preg_replace("/\s[A-Z]\s || \s[A-Z] || [A-Z]\s || \s[a-z]\s/", "", $str);

        return $str;
    }

    /**
     * A function for building the tags cloud
     * @param string $str the clear string without punctuation
     * @return string
     */
    public function buildCloud($str)
    {
        $arrayBuff = explode(" ", $str);
        $arrayWord = array_count_values($arrayBuff);
        $cloud = '';

        foreach($arrayWord as $word => $count)
        {
            if ($word == "")
            {
                continue;
            }

            if ($this->countFilterWord < 80)
            {
                $this->countFilterWord++;
            }
            else
            {
                break;
            }

            $size = $count * 8;

            if ($size > 30)
            {
                $size = 20;
            }

            $cloud .= '<span style="font-size:' . $size . 'pt"><a href=' . 'filter/' . $word . '>' . $word . '</a></span> ';
        }

        return $cloud;
    }
}

?>
<?php
class UNL_WDN_Assessment_Filter_FileExtension extends Spider_UriFilterInterface
{
    function accept()
    {
        $path_parts = pathinfo($this->current());
        if (!isset($path_parts['extension'])
            || $path_parts['extension'] == 'html'
            || $path_parts['extension'] == 'php'
            || $path_parts['extension'] == 'shtml'
            || $path_parts['extension'] == 'asp'
            || $path_parts['extension'] == 'aspx'
            || $path_parts['extension'] == 'jsp') {
            return true;
        }
        return false;
    }
}
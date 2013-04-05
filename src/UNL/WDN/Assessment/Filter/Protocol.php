<?php
class UNL_WDN_Assessment_Filter_Protocol extends Spider_UriFilterInterface
{
    function accept()
    {
        //$this->current() contains the base uri, so the 'protocol' it probably won't start with the following.
        if (stripos($this->current(), 'javascript:') !== false
            || stripos($this->current(), 'tel:') !== false
            || stripos($this->current(), 'mailto:') !== false) {
            return false;
        }
        
        return true;
    }
}
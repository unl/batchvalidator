<?php
class UNL_WDN_Assessment_Filter_Protocol extends Spider_UriFilterInterface
{
    function accept()
    {

        if (stripos($this->current(), 'javascript:') === 0
            || stripos($this->current(), 'tel:') === 0
            || stripos($this->current(), 'mailto:') === 0) {
            
            return false;
        }
        
        return true;
    }
}
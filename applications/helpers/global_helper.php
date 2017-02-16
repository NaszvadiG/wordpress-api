<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


    function currency($value){
		return currency_format().' '.number_format($value);
    }

    function currency_format(){
    	//return '&#3647';
    	return 'IDR';
    }
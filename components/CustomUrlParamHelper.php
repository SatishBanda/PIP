<?php

/**
 * 
 * @author Naveen R
 *
 */

namespace app\components;

use yii\base\Component;

class CustomUrlParamHelper extends Component {


	public static function encode($raw_str) {
        // the date salt will cause the url to expire after 1 day
		//$salt = date("dmY");
		//$salt = "fish";
		//$encoded_str = trim(base64_encode(mcrypt_encrypt(MCRYPT_BLOWFISH, $salt, $raw_str, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB), MCRYPT_RAND))));
		//$encoded_str = trim(base64_encode(mcrypt_encrypt(MCRYPT_BLOWFISH, $salt, $raw_str, MCRYPT_MODE_ECB)));
		//strtr($encoded_str, '+/=', '-.#');
		
		//return rtrim(strtr($encoded_str, '+/=', '-.#'),'#');
		return $raw_str;
		
		

   }

    public static function decode($encoded_str) {
        // the date salt will cause the url to expire after 1 day
		//$salt = date("dmY");
		//$salt = "fish";
		//$decoded_str = trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $salt, base64_decode(strtr($encoded_str, '-.#', '+/=')), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB), MCRYPT_RAND)));
		//$decoded_str = trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $salt, base64_decode(strtr($encoded_str, '-.#', '+/='), '#'), MCRYPT_MODE_ECB));
		//return $decoded_str;
	    return $raw_str;
	}

	
	
}

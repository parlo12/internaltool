<?php
// Phone validation and E.164 formatting helper
namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;

class PhoneHelper
{
    public static function validateAndFormat($phone, $defaultRegion = 'US')
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $numberProto = $phoneUtil->parse($phone, $defaultRegion);
            if (!$phoneUtil->isValidNumber($numberProto)) {
                return [false, null];
            }
            $e164 = $phoneUtil->format($numberProto, PhoneNumberFormat::E164);
            return [true, $e164];
        } catch (NumberParseException $e) {
            Log::error('NumberParseException: ' . $e->getMessage());
            return [false, $e->getMessage()];
        }
    }
}

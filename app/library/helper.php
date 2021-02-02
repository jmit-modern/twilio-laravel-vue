<?php

use App\Models\Categories;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Session as Session;
use DB as DB;



if ( ! function_exists('getVatPercent')) {
    function getVatPercent($consultantProfile, $authCustomerProfile): int
    {
//        \Log::info(  varDump($consultantProfile->id, ' -10 $consultantProfile->id::') );
        $consultantProfileCountry = $consultantProfile->country;
//        \Log::info(varDump($consultantProfileCountry, ' -2 $consultantProfileCountry::'));
        $authCustomerProfileCountry = $authCustomerProfile->country;
//        \Log::info(varDump($authCustomerProfileCountry, ' -2 $authCustomerProfileCountry::'));

        $norway_country_label = config('app.norway_country_label');
        if ( $norway_country_label == $consultantProfileCountry or $norway_country_label == $authCustomerProfileCountry ) {
//            \Log::info(varDump($consultantProfile->profession, ' -1 $consultantProfile->profession::'));
            $consultantCategory = Categories::getByCategoryName($consultantProfile->profession)->first();
            if ( ! empty($consultantCategory->vat)) {
                return $consultantCategory->vat;
            }
        }
        return 0;
    }
} // if ( ! function_exists('getVatPercent')) {


if ( ! function_exists('calcCommonMissedNotificationsCount')) {
    function calcCommonMissedNotificationsCount(): int
    {
        $retValue            = 0;
        $missedNotifications = Session::get('missedNotifications');
        if (empty($missedNotifications) or ! is_array($missedNotifications)) {
            $missedNotifications = [];
        }
        foreach ($missedNotifications as $nextMissedNotification) {
            if ( ! empty($nextMissedNotification['sender_total'])) {
                $retValue += $nextMissedNotification['sender_total'];
            }
        }

        return $retValue;
    }

} // if ( ! function_exists('calcCommonMissedNotificationsCount')) {


if ( ! function_exists('varDump')) {
    function varDump($var, $descr = '', bool $return_string = true)
    {
        if (is_null($var)) {
            $output_str = 'NULL :' . (! empty($descr) ? $descr . ' : ' : '') . 'NULL';
            if ($return_string) {
                return $output_str;
            }
            \Log::info($output_str);

            return;
        }
        if (is_scalar($var)) {
            $output_str = 'scalar => (' . gettype($var) . ') :' . (! empty($descr) ? $descr . ' : ' : '') . $var;
            if ($return_string) {
                return $output_str;
            }
            \Log::info($output_str);

            return;
        }

        if (is_array($var)) {
            $output_str = 'Array(' . count($var) . ') :' . (! empty($descr) ? $descr . ' : ' : '') . print_r($var,
                    true);
            if ($return_string) {
                return $output_str;
            }

            return;
        }

        if (class_basename($var) === 'Request' or class_basename($var) === 'LoginRequest') {
            $request     = request();
            $requestData = $request->all();
            $output_str  = 'Request:' . (! empty($descr) ? $descr . ' : ' : '') . print_r($requestData, true);
            if ($return_string) {
                return $output_str;
            }
            \Log::info($output_str);

            return;
        }

        if (class_basename($var) === 'LengthAwarePaginator' or class_basename($var) === 'Collection') {
            $collectionClassBasename = '';
            if (isset($var[0])) {
                $collectionClassBasename = class_basename($var[0]);
            }
            $output_str = ' Collection(' . count($var->toArray()) . ' of ' . $collectionClassBasename . ') :' . (! empty($descr) ? $descr . ' : ' : '') . print_r($var->toArray(),
                    true);
            if ($return_string) {
                return $output_str;
            }
            \Log::info($output_str);

            return;
        }

        if (gettype($var) === 'object') {
            if (is_subclass_of($var, 'Illuminate\Database\Eloquent\Model')) {
                $output_str = ' (Model Object of ' . get_class($var) . ') :' . (! empty($descr) ? $descr . ' : ' : '') . print_r($var/*->getAttributes()*/ ->toArray(),
                        true);
                if ($return_string) {
                    return $output_str;
                }
                \Log::info($output_str);

                return;
            }
            $output_str = ' (Object of ' . get_class($var) . ') :' . (! empty($descr) ? $descr . ' : ' : '') . print_r((array)$var,
                    true);
            if ($return_string) {
                return $output_str;
            }
            \Log::info($output_str);

            return;
        }
    }
} // if ( ! function_exists('varDump')) {


<?php

if (! function_exists('reformatDate')) {
    function reformatDate($dateTime = null): ?string
    {
        return $dateTime ? Carbon\Carbon::parse($dateTime)->locale(app()->getLocale())->translatedFormat('d F Y h:i A') : null;
    }
}

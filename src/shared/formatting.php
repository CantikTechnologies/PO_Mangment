<?php
/**
 * Shared formatting functions
 * This file contains common formatting utilities used across the application
 */

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return '₹ ' . number_format((float)$amount, 2, '.', ',');
    }
}

if (!function_exists('formatDate')) {
    function formatDate($excel_date) {
        if (empty($excel_date)) return '-';
        $unix_date = ($excel_date - 25569) * 86400;
        $formatted = gmdate('d M Y', $unix_date);
        return strtolower($formatted);
    }
}

if (!function_exists('formatPercentage')) {
    function formatPercentage($value, $decimals = 1) {
        return number_format((float)$value, $decimals) . '%';
    }
}

if (!function_exists('badgePctClass')) {
    function badgePctClass($pct) {
        if ($pct >= 90) return 'bg-green-100 text-green-800';
        if ($pct >= 70) return 'bg-yellow-100 text-yellow-800';
        return 'bg-red-100 text-red-800';
    }
}

if (!function_exists('getBadgeClass')) {
    function getBadgeClass($status) {
        switch (strtolower($status)) {
            case 'paid': 
            case 'complete': 
            case 'closed': 
                return 'bg-green-100 text-green-800';
            case 'pending': 
            case 'incomplete': 
            case 'open': 
                return 'bg-yellow-100 text-yellow-800';
            case 'overdue': 
            case 'failed': 
                return 'bg-red-100 text-red-800';
            case 'partial': 
                return 'bg-blue-100 text-blue-800';
            default: 
                return 'bg-gray-100 text-gray-800';
        }
    }
}

if (!function_exists('excelToDate')) {
    function excelToDate($excel_date) {
        if (empty($excel_date)) return '';
        $unix_date = ($excel_date - 25569) * 86400;
        $formatted = date('d M Y', $unix_date);
        return strtolower($formatted);
    }
}
?>

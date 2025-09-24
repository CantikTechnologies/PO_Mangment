<?php
/**
 * Indian Numbering System Functions
 * Formats numbers according to Indian numbering system (lakh/crore)
 */

function formatIndianNumber($number, $decimals = 2) {
    if (!is_numeric($number)) {
        return '0.' . str_repeat('0', $decimals);
    }
    
    $number = (float)$number;
    $number = round($number, $decimals);
    
    // Get the integer part
    $integerPart = (int)$number;
    $decimalPart = $number - $integerPart;
    
    // Convert integer part to string and reverse for processing
    $str = strrev((string)$integerPart);
    $result = '';
    
    // Apply Indian numbering system
    for ($i = 0; $i < strlen($str); $i++) {
        if ($i == 3) {
            $result .= ','; // First comma after 3 digits (thousands)
        } elseif ($i > 3 && ($i - 3) % 2 == 0) {
            $result .= ','; // Subsequent commas every 2 digits (lakhs, crores)
        }
        $result .= $str[$i];
    }
    
    // Reverse back to normal order
    $result = strrev($result);
    
    // Add decimal part if needed
    if ($decimals > 0) {
        $decimalStr = number_format($decimalPart, $decimals, '.', '');
        $decimalStr = substr($decimalStr, 1); // Remove the leading '0'
        $result .= $decimalStr;
    }
    
    return $result;
}

function formatCurrency($amount) {
    if (!is_numeric($amount)) {
        return '₹0.00';
    }
    
    $amount = (float)$amount;
    
    // Handle negative amounts
    $isNegative = $amount < 0;
    $amount = abs($amount);
    
    // Format with Indian numbering system (proper Indian comma placement)
    $formatted = '₹' . formatIndianNumber($amount, 2);
    
    return $isNegative ? '-' . $formatted : $formatted;
}

function formatCurrencyPlain($amount) {
    if (!is_numeric($amount)) {
        return '0.00';
    }
    
    $amount = (float)$amount;
    
    // Handle negative amounts
    $isNegative = $amount < 0;
    $amount = abs($amount);
    
    // Format with Indian numbering system (proper Indian comma placement)
    $formatted = formatIndianNumber($amount, 2);
    
    return $isNegative ? '-' . $formatted : $formatted;
}

function formatNumber($number) {
    if (!is_numeric($number)) {
        return '0';
    }
    
    $number = (float)$number;
    
    // Handle negative numbers
    $isNegative = $number < 0;
    $number = abs($number);
    
    // Format with Indian numbering system (proper Indian comma placement)
    $formatted = formatIndianNumber($number, 2);
    
    return $isNegative ? '-' . $formatted : $formatted;
}

function formatPercentage($value) {
    return is_numeric($value) ? number_format((float)$value, 2) . '%' : '-';
}

function badgePctClass($pct) {
    $v = is_numeric($pct) ? (float)$pct : 0.0;
    if ($v >= 20) return 'bg-green-100 text-green-700';
    if ($v >= 10) return 'bg-amber-100 text-amber-700';
    return 'bg-red-100 text-red-700';
}
?>

/**
 * Indian Numbering System JavaScript Functions
 * Formats numbers according to Indian numbering system (lakh/crore)
 */

function formatCurrencyIndian(amount) {
    if (!amount || isNaN(amount)) {
        return '₹0.00';
    }
    
    const num = parseFloat(amount);
    const isNegative = num < 0;
    const absNum = Math.abs(num);
    
    // Format with Indian numbering system (comma separators)
    const formatted = '₹' + absNum.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    return isNegative ? '-' + formatted : formatted;
}

function formatNumberIndian(number) {
    if (!number || isNaN(number)) {
        return '0';
    }
    
    const num = parseFloat(number);
    const isNegative = num < 0;
    const absNum = Math.abs(num);
    
    // Format with Indian numbering system (comma separators)
    const formatted = absNum.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    return isNegative ? '-' + formatted : formatted;
}

function formatCurrencyPlainIndian(amount) {
    if (!amount || isNaN(amount)) {
        return '0.00';
    }
    
    const num = parseFloat(amount);
    const isNegative = num < 0;
    const absNum = Math.abs(num);
    
    // Format with Indian numbering system (comma separators, without ₹ symbol)
    const formatted = absNum.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    return isNegative ? '-' + formatted : formatted;
}

// Update display elements with Indian numbering
function updateDisplayWithIndianNumbering() {
    // Update all elements with data-indian-format attribute
    document.querySelectorAll('[data-indian-format]').forEach(element => {
        const value = element.textContent || element.value;
        if (value && !isNaN(parseFloat(value))) {
            const formatted = formatCurrencyIndian(value);
            element.textContent = formatted;
        }
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    updateDisplayWithIndianNumbering();
});

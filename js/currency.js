const exchangeRates = {
    "IDR": 1,
    "USD": 0.000065,
    "MYR": 0.00030,
    "SGD": 0.000087
};

const currencySymbols = {
    "IDR": "Rp",
    "USD": "$",
    "MYR": "RM",
    "SGD": "S$"
};

let currentCurrency = 'IDR';

function formatCurrency(amount, currency) {
    const symbol = currencySymbols[currency];
    let formattedAmount;

    if (currency === 'IDR') {
        formattedAmount = new Intl.NumberFormat('id-ID').format(amount);
    } else {
        formattedAmount = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount);
    }
    
    return `${symbol} ${formattedAmount}`;
}

function updatePrices(currency) {
    currentCurrency = currency;
    const priceElements = document.querySelectorAll('[data-original-price]');
    
    priceElements.forEach(element => {
        const originalPrice = parseFloat(element.getAttribute('data-original-price'));
        const convertedPrice = originalPrice * exchangeRates[currency];
        
        // Update the main text node, keeping any child elements (like span for description) intact if possible,
        // but here the price is the main text. 
        // Strategy: The element structure is <p>PRICE <span>(Desc)</span></p>
        // Use logic to replace only the text node.
        
        const nodes = element.childNodes;
        let textNodeUpdated = false;
        
        nodes.forEach(node => {
            if (node.nodeType === Node.TEXT_NODE && node.textContent.trim().length > 0 && !textNodeUpdated) {
                // Determine if this text node contains the price
                // Simple heuristic: just replace the first non-empty text node
                node.textContent = formatCurrency(convertedPrice, currency) + ' '; 
                textNodeUpdated = true;
            }
        });
        
        // Fallback if no text node found (e.g. if structure changes), simpler replacement
        if (!textNodeUpdated) {
             // Caution: this might wipe the span. 
             // Ideally we structure HTML as <span class="price-val">...</span>
             // Let's assume I will restructure HTML to have a specific span for price value to be safe.
        }
    });

    // Update active state of selector if exists
    document.querySelectorAll('.currency-btn').forEach(btn => {
        if (btn.dataset.currency === currency) {
            btn.classList.add('bg-sutan-gold', 'text-sutan-dark', 'font-bold');
            btn.classList.remove('bg-white', 'text-gray-600');
        } else {
            btn.classList.remove('bg-sutan-gold', 'text-sutan-dark', 'font-bold');
            btn.classList.add('bg-white', 'text-gray-600');
        }
    });
}

function initCurrencySelector() {
    // Check if selector exists, if not create it (or rely on HTML)
}

document.addEventListener('DOMContentLoaded', () => {
   // Default to IDR
   updatePrices('IDR');
});

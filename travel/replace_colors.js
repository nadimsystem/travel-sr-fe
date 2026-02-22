const fs = require('fs');
const path = require('path');

const files = [
  'BookingForm.vue',
  'BookingList.vue',
  'BookingHistory.vue'
];

const basePath = '/Applications/XAMPP/xamppfiles/htdocs/travel-sr-fe/travel/src/components';

files.forEach(file => {
  const filePath = path.join(basePath, file);
  let content = fs.readFileSync(filePath, 'utf-8');

  // Colors replacement
  content = content.replace(/bg-blue-600/g, 'bg-sutan-gold');
  content = content.replace(/bg-blue-500/g, 'bg-yellow-500');
  content = content.replace(/hover:bg-blue-700/g, 'hover:bg-yellow-600');
  content = content.replace(/hover:bg-blue-600/g, 'hover:bg-sutan-gold');
  content = content.replace(/hover:bg-blue-50/g, 'hover:bg-yellow-50');
  content = content.replace(/bg-blue-50/g, 'bg-yellow-50');
  
  content = content.replace(/text-blue-600/g, 'text-sutan-gold');
  content = content.replace(/text-blue-500/g, 'text-yellow-600');
  content = content.replace(/text-blue-700/g, 'text-yellow-700');
  content = content.replace(/text-blue-400/g, 'text-yellow-500');
  content = content.replace(/text-blue-800/g, 'text-yellow-800');
  content = content.replace(/text-blue-900/g, 'text-yellow-600');
  
  content = content.replace(/border-blue-600/g, 'border-sutan-gold');
  content = content.replace(/border-blue-500/g, 'border-yellow-500');
  content = content.replace(/border-blue-200/g, 'border-yellow-200');
  content = content.replace(/border-blue-100/g, 'border-yellow-100');
  
  content = content.replace(/ring-blue-500/g, 'ring-sutan-gold');
  content = content.replace(/ring-blue-100/g, 'ring-yellow-100');
  
  content = content.replace(/shadow-blue-500\/30/g, 'shadow-sutan-gold/30');

  // Other aesthetic replacements
  content = content.replace(/'#3b82f6'/g, "'#D4AF37'"); // Sweetalert confirm color
  
  fs.writeFileSync(filePath, content, 'utf-8');
});
console.log('Colors replaced successfully!');

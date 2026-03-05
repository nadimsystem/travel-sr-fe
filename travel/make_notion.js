const fs = require('fs');

async function sed(file, replacements) {
    if(!fs.existsSync(file)) return console.log(file, 'not found');
    let content = fs.readFileSync(file, 'utf8');
    for(let r of replacements) {
        content = content.replace(r.from, r.to);
    }
    fs.writeFileSync(file, content);
    console.log('Processed', file);
}

const landingRepl = [
    // Background gradient around logo
    {
        from: /absolute inset-0 bg-gradient-to-tr from-[a-zA-Z0-9-\/]* to-[a-zA-Z0-9-\/]* rounded-[a-zA-Z0-9-]+ blur-[a-zA-Z0-9-]+[ \w-:]*/g,
        to: "absolute inset-0 bg-transparent"
    },
    {
        from: /absolute inset-0 bg-blue-100 rounded-3xl blur-xl opacity-50 transform scale-110/g,
        to: "absolute inset-0 bg-transparent"
    },
    {
        from: /bg-white\/90 backdrop-blur-md p-5 rounded-\[.*\].*/g,
        to: "bg-white p-4 rounded-xl border border-slate-200 shadow-sm relative z-10 w-28 h-28 mx-auto flex items-center justify-center transform transition-all hover:-translate-y-1"
    },
    {
        from: /text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-600/g,
        to: "text-slate-900"
    },
    // Primary Button
    {
        from: /bg-[a-zA-Z0-9-]+ text-white rounded-2xl p-4 shadow-[a-zA-Z0-9-\/]+.*/g,
        to: "bg-slate-900 text-white rounded-xl p-4 shadow-sm hover:shadow hover:-translate-y-0.5 transition-all text-left border border-slate-900"
    },
    {
        from: /bg-gradient-to-r from-blue-600 to-indigo-600[ \S]*/g,
        to: "bg-slate-900 text-white rounded-xl p-4 shadow-sm hover:shadow hover:-translate-y-0.5 transition-all text-left border border-slate-900"
    },
    // Removing group-hover:bg-white etc inside button
    {
        from: /w-10 h-10 rounded-full bg-white\/20 flex items-center justify-center backdrop-blur-sm group-hover:bg-white group-hover:text-[a-zA-Z0-9-]+ transition-colors/g,
        to: "w-8 h-8 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm group-hover:bg-white/30 transition-colors"
    },
    // Secondary button
    {
        from: /bg-white border-1.5 border-slate-200 text-slate-700 rounded-2xl p-4/g,
        to: "bg-white border border-slate-200 text-slate-800 rounded-xl p-4"
    },
    {
        from: /bg-white\/80 backdrop-blur-sm border-2 border-[a-zA-Z0-9-]+ text-[a-zA-Z0-9-]+ rounded-[a-zA-Z0-9-]+ p-4[ \S]*/g,
        to: "bg-white border border-slate-200 text-slate-800 rounded-xl p-4 shadow-sm hover:border-slate-300 hover:bg-slate-50 transition-all transform text-left group flex items-center gap-4 cursor-pointer"
    },
    // Titles
    {
        from: /text-yellow-600/g,
        to: "text-slate-900"
    },
    {
        from: /text-yellow-\d+/g,
        to: "text-slate-300"
    }
];

const appRepl = [
    // Base wrapper clean
    {
        from: /bg-slate-50 relative overflow-hidden/g,
        to: "bg-white"
    },
    {
        from: /bg-white\/50 backdrop-blur-xl md:my-6 md:rounded-3xl shadow-2xl md:min-h-0 border border-white/g,
        to: "bg-white md:my-6 md:rounded-xl shadow-sm md:min-h-0 border border-slate-200"
    },
    {
        from: /app-header rounded-[a-zA-Z0-9-]+/g,
        to: "app-header"
    },
    // Remove dynamic background blobs
    {
        from: /<!-- Dynamic Background Blobs -->[\s\S]*?<\/div>[\s\S]*?<\/div>/, // Will match the absolute wrapper and components closely
        to: ""
    },
    // Fix text coloring
    {
        from: /text-transparent bg-clip-text bg-gradient-to-r from-blue-700 to-indigo-600/g,
        to: "text-slate-900"
    },
    // Desktop Nav
    {
        from: /bg-slate-100\/50 p-1.5 rounded-2xl border border-white/g,
        to: "bg-slate-50 p-1 rounded-lg border border-slate-200"
    },
    {
        from: /bg-white text-blue-600 shadow-sm border border-slate-100/g,
        to: "bg-white text-slate-900 shadow-sm border border-slate-200"
    },
    // Middle background
    {
        from: /main-content min-h-screen/g,
        to: "main-content min-h-screen bg-white"
    },
    {
        from: /main-content bg-slate-50/g,
        to: "main-content bg-white"
    },
    // Floating Plus button gradient removal
    {
        from: /bg-gradient-to-tr from-blue-600 to-indigo-500/g,
        to: "bg-slate-900"
    },
    {
        from: /shadow-lg shadow-blue-[a-zA-Z0-9-\/]+/g,
        to: "shadow-sm"
    },
    {
        from: /text-blue-600/g,
        to: "text-slate-900"
    },
    {
        from: /ring-blue-300/g,
        to: "ring-slate-300"
    }
];

const bookListRepl = [
    {
        from: /bg-slate-50 font-sans/g,
        to: "bg-white font-sans"
    },
    // Route cards
    {
        from: /bg-white rounded-\[1.5rem\] shadow-sm border border-slate-200/g,
        to: "bg-white rounded-xl shadow-sm border border-slate-200"
    },
    {
        from: /bg-slate-50 border-b border-slate-100/g,
        to: "bg-white border-b border-slate-200"
    },
    {
        from: /bg-blue-100 text-blue-600/g,
        to: "bg-slate-100 text-slate-600 border border-slate-200"
    },
    // Batch cards
    {
        from: /bg-slate-50\/50 rounded-3xl border-2 border-slate-100 hover:border-blue-400 group transition-all duration-300 hover:shadow-xl hover:shadow-blue-500\/10/g,
        to: "bg-white rounded-xl border border-slate-200 hover:border-slate-300 group transition-all duration-300 shadow-sm"
    },
    {
        from: /bg-slate-50 rounded-2xl border border-slate-200/g,
        to: "bg-white rounded-xl border border-slate-200 box-border hover:border-slate-300"
    },
    // Miniature Seat Map
    {
        from: /bg-\[#f8fafc\] flex-1/g,
        to: "bg-white flex-1"
    },
    {
        from: /bg-slate-100 p-4 rounded-xl border border-slate-200/g,
        to: "bg-slate-50 p-4 rounded-lg border border-slate-200"
    },
    // Drop the gradients in scheduling loop completely
    {
        from: /group-hover:bg-blue-50\/50 transition-colors/g,
        to: "group-hover:bg-slate-50 transition-colors"
    },
    {
        from: /<div class="absolute inset-x-0 bottom-0 h-1\/2 bg-gradient-to-t from-slate-100\/50 to-transparent pointer-events-none"><\/div>/g,
        to: ""
    },
    {
        from: /<div class="absolute inset-0 bg-gradient-to-r from-blue-100\/0 to-blue-100\/30 opacity-0 group-hover:opacity-100 transition-opacity"><\/div>/g,
        to: ""
    },
    {
        from: /text-transparent bg-clip-text bg-gradient-to-tr from-slate-800 to-slate-500 group-hover:from-blue-700 group-hover:to-indigo-500 transition-all/g,
        to: "text-slate-900 transition-all"
    },
    {
        from: /bg-blue-100\/80 text-blue-600/g,
        to: "bg-slate-100 text-slate-800"
    },
    // Buttons
    {
        from: /bg-blue-600 text-white/g,
        to: "bg-slate-900 border border-slate-900 text-white"
    }
];

const bookFormRepl = [
    // Step indicators
    {
        from: /bg-blue-600 text-white shadow-md shadow-blue-[a-zA-Z0-9-\/]+/g,
        to: "bg-slate-900 text-white shadow-sm"
    },
    {
        from: /bg-green-500 text-white shadow-sm/g,
        to: "bg-slate-200 text-slate-800 shadow-sm border border-slate-300"
    },
    // Buttons main form
    {
        from: /text-\[#00c853\]/g,
        to: "text-slate-700"
    },
    {
        from: /text-\[#ff6b00\]/g,
        to: "text-slate-700"
    },
    {
        from: /text-[#ff0000]/gi,
        to: "text-slate-700"
    },
    {
        from: /w-full p-4 bg-blue-600 text-white rounded-2xl/g,
        to: "w-full p-4 bg-slate-900 text-white rounded-xl"
    },
    {
        from: /shadow-[a-zA-Z0-9-\/]+ hover:shadow-lg hover:-translate-y-1 transition-all/g,
        to: "shadow-sm hover:shadow-md transition-all"
    },
    {
        from: /bg-blue-50 border-blue-200 text-blue-700/g,
        to: "bg-slate-100 border-slate-300 text-slate-900"
    },
    {
        from: /hover:border-blue-300/g,
        to: "hover:border-slate-400"
    },
    {
        from: /focus:border-blue-500 focus:ring-blue-500/g,
        to: "focus:border-slate-800 focus:ring-slate-800/10"
    },
    {
        from: /border-slate-200 rounded-\[1.5rem\]/g,
        to: "border-slate-200 rounded-xl"
    }
]

sed('src/components/LandingPage.vue', landingRepl);
sed('src/App.vue', appRepl);
sed('src/components/BookingList.vue', bookListRepl);
sed('src/components/BookingForm.vue', bookFormRepl);


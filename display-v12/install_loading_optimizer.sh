#!/bin/bash

###############################################
# SUTAN RAYA LOADING OPTIMIZER
# Auto-installer untuk semua file PHP
###############################################

echo "🚀 Installing Loading Optimizer..."

# Define the script tag to insert
SCRIPT_TAG='    <!-- Loading Optimizer - Prevents visual flash & UI errors -->
    <script src="js/loading-optimizer.js"></script>
'

# Array of files to update
files=(
    "dispatcher.php"
    "booking_management.php"
    "booking_travel.php"
    "booking_bus.php"
    "schedule.php"
    "assets.php"
    "route_management.php"
    "reports.php"
    "penagihan.php"
    "pembatalan.php"
    "manifest.php"
)

count=0

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        # Check if already installed
        if grep -q "loading-optimizer.js" "$file"; then
            echo "⏭️  Skipping $file (already installed)"
        else
            # Find the line with first <script> tag and insert before it
            if [[ "$OSTYPE" == "darwin"* ]]; then
                # macOS
                sed -i '' '/<script/i\
'"$SCRIPT_TAG"'
' "$file"
            else
                # Linux
                sed -i '/<script/i\'"$SCRIPT_TAG" "$file"
            fi
            echo "✅ Installed to $file"
            ((count++))
        fi
    else
        echo "⚠️  File not found: $file"
    fi
done

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✨ Installation Complete!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 Files updated: $count"
echo ""
echo "💡 Tips:"
echo "   • Clear browser cache for best results"
echo "   • Check console (F12) for performance stats"
echo "   • To uninstall, just delete js/loading-optimizer.js"
echo ""
echo "🎉 Your app is now optimized!"

#!/bin/bash

###############################################
# SUTAN RAYA LOADING OPTIMIZER
# Uninstaller - menghapus loading optimizer
###############################################

echo "🗑️  Uninstalling Loading Optimizer..."

# Array of files to update
files=(
    "paket.php"
    "package_shipping.php"
    "index.php"
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
        # Check if loading optimizer is installed
        if grep -q "loading-optimizer.js" "$file"; then
            # Remove the loading optimizer lines
            if [[ "$OSTYPE" == "darwin"* ]]; then
                # macOS
                sed -i '' '/Loading Optimizer/,/<script src="js\/loading-optimizer.js">/d' "$file"
            else
                # Linux
                sed -i '/Loading Optimizer/,/<script src="js\/loading-optimizer.js">/d' "$file"
            fi
            echo "✅ Removed from $file"
            ((count++))
        else
            echo "⏭️  Skipping $file (not installed)"
        fi
    else
        echo "⚠️  File not found: $file"
    fi
done

# Ask if user wants to delete the JS file
echo ""
read -p "❓ Delete js/loading-optimizer.js file? (y/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    if [ -f "js/loading-optimizer.js" ]; then
        rm "js/loading-optimizer.js"
        echo "✅ Deleted js/loading-optimizer.js"
    else
        echo "⚠️  File js/loading-optimizer.js not found"
    fi
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✨ Uninstallation Complete!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 Files cleaned: $count"
echo ""
echo "💡 App is back to original state"

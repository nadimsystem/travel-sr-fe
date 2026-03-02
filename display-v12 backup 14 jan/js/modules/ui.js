export const uiMixin = {
    methods: {
        changeView(v) { 
            const map = {
                dashboard: 'dashboard.php',
                bookingManagement: 'booking_management.php',
                bookingTravel: 'booking_travel.php',
                bookingBus: 'booking_bus.php',
                dispatcher: 'dispatcher.php',
                manifest: 'manifest.php',
                assets: 'assets.php',
                routeManagement: 'route_management.php',
                packageShipping: 'package_shipping.php'
            };
            if (map[v]) {
                window.location.href = map[v];
            } else {
                this.view = v; 
            }
        },
        toggleDarkMode() {
            this.isDarkMode = !this.isDarkMode;
            if (this.isDarkMode) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
            localStorage.setItem('sutan_v10_dark', this.isDarkMode);
        },

        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch((e) => console.log(e));
                this.isFullscreen = true;
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                    this.isFullscreen = false;
                }
            }
        }
    }
};
